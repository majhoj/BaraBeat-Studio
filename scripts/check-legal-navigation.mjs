import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const navigationSource = fs.readFileSync(path.join(projectRoot, 'legal/navigation.js'), 'utf8');
const origin = 'https://barabeat.test';

function createWindow(name, href = origin + '/' + name + '.html') {
  const assigned = [];
  const opened = [];
  const targetWindow = {
    name,
    closed: false,
    focused: false,
    closeCalled: false,
    location: {
      origin,
      href,
      assign(href) {
        assigned.push(href);
      }
    },
    focus() {
      targetWindow.focused = true;
    },
    close() {
      targetWindow.closeCalled = true;
    },
    open(href, targetName) {
      const childWindow = createWindow(targetName || '_blank');
      childWindow.initialHref = href;
      opened.push(childWindow);
      return childWindow;
    },
    setTimeout,
    clearTimeout
  };
  targetWindow.parent = targetWindow;
  targetWindow.opener = null;
  targetWindow.assigned = assigned;
  targetWindow.opened = opened;
  return targetWindow;
}

function createLink(attributes) {
  const values = Object.assign({}, attributes);
  return {
    href: values.href || '',
    target: values.target || '',
    hasAttribute(name) {
      return Object.prototype.hasOwnProperty.call(values, name);
    },
    getAttribute(name) {
      return Object.prototype.hasOwnProperty.call(values, name) ? values[name] : null;
    }
  };
}

function createScenario(options = {}) {
  const currentWindow = options.currentWindow || createWindow('current');
  const listeners = {};
  const document = {
    addEventListener(type, listener) {
      listeners[type] = listener;
    }
  };
  const fetchCalls = [];
  const fetchImpl = options.fetchImpl || function (href) {
    fetchCalls.push(href);
    return Promise.resolve({ ok: true });
  };
  const context = vm.createContext({
    window: currentWindow,
    document,
    fetch(href, requestOptions) {
      fetchCalls.push({ href, requestOptions });
      return fetchImpl(href, requestOptions);
    },
    console,
    URL
  });
  vm.runInContext(navigationSource, context, { filename: 'legal/navigation.js' });
  return { currentWindow, listeners, fetchCalls };
}

function click(scenario, link) {
  let prevented = false;
  scenario.listeners.click({
    target: {
      closest() {
        return link;
      }
    },
    preventDefault() {
      prevented = true;
    }
  });
  return prevented;
}

async function flushPromises() {
  await Promise.resolve();
  await Promise.resolve();
  await new Promise(function (resolve) {
    setImmediate(resolve);
  });
}

{
  const appWindow = createWindow('app', origin + '/index.php');
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  manualWindow.opener = appWindow;
  const scenario = createScenario({ currentWindow: manualWindow });
  const prevented = click(scenario, createLink({
    href: '../../index.php',
    'data-barabeat-return': ''
  }));
  assert.equal(prevented, true);
  assert.equal(appWindow.focused, true);
  assert.equal(manualWindow.closeCalled, true);
  assert.equal(scenario.fetchCalls.length, 0);
}

{
  const appWindow = createWindow('app', origin + '/index.php');
  const scenario = createScenario({ currentWindow: appWindow });
  click(scenario, createLink({
    href: 'manual/offline/de.html',
    target: 'barabeatManual',
    'data-barabeat-open-window': ''
  }));
  assert.equal(appWindow.opened.length, 1);
  assert.equal(appWindow.opened[0].initialHref, origin + '/manual/offline/de.html');
  assert.equal(appWindow.opened[0].opener, appWindow);
}

{
  const appWindow = createWindow('app', origin + '/index.php');
  const scenario = createScenario({ currentWindow: appWindow });
  click(scenario, createLink({
    href: 'legal/offline/impressum.html',
    target: '_blank',
    'data-online-href': 'impressum.php'
  }));
  await flushPromises();
  assert.equal(appWindow.opened.length, 1);
  assert.equal(appWindow.opened[0].initialHref, origin + '/legal/offline/impressum.html');
  assert.deepEqual(appWindow.opened[0].assigned, [origin + '/impressum.php']);
  assert.equal(appWindow.opened[0].opener, appWindow);
}

{
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  const scenario = createScenario({
    currentWindow: manualWindow,
    fetchImpl() {
      return Promise.reject(new Error('offline'));
    }
  });
  click(scenario, createLink({
    href: '../../legal/offline/datenschutz.html',
    target: '_blank',
    rel: 'opener',
    'data-online-href': '../../datenschutz.php'
  }));
  await flushPromises();
  assert.equal(manualWindow.opened.length, 1);
  assert.equal(manualWindow.opened[0].initialHref, origin + '/legal/offline/datenschutz.html');
  assert.deepEqual(manualWindow.opened[0].assigned, []);
  assert.equal(manualWindow.opened[0].opener, manualWindow);
}

{
  const appWindow = createWindow('app', origin + '/index.php');
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  manualWindow.opener = appWindow;
  const scenario = createScenario({ currentWindow: manualWindow });
  click(scenario, createLink({
    href: '../../legal/offline/impressum.html',
    target: '_self',
    'data-online-href': '../../impressum.php'
  }));
  await flushPromises();
  assert.equal(manualWindow.opened.length, 0);
  assert.deepEqual(manualWindow.assigned, [origin + '/impressum.php']);
}

{
  const appWindow = createWindow('app', origin + '/index.php');
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  manualWindow.opener = appWindow;
  const scenario = createScenario({
    currentWindow: manualWindow,
    fetchImpl() {
      return Promise.reject(new Error('offline'));
    }
  });
  click(scenario, createLink({
    href: '../../legal/offline/datenschutz.html',
    target: '_self',
    'data-online-href': '../../datenschutz.php'
  }));
  await flushPromises();
  assert.equal(manualWindow.opened.length, 0);
  assert.deepEqual(manualWindow.assigned, [origin + '/legal/offline/datenschutz.html']);
}

{
  let runTimeout = null;
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  manualWindow.setTimeout = function (callback) {
    runTimeout = callback;
    return 1;
  };
  manualWindow.clearTimeout = function () {};
  const scenario = createScenario({
    currentWindow: manualWindow,
    fetchImpl() {
      return new Promise(function () {});
    }
  });
  click(scenario, createLink({
    href: '../../legal/offline/impressum.html',
    target: '_self',
    'data-online-href': '../../impressum.php'
  }));
  assert.equal(typeof runTimeout, 'function');
  runTimeout();
  await flushPromises();
  assert.deepEqual(manualWindow.assigned, [origin + '/legal/offline/impressum.html']);
}

{
  const manualWindow = createWindow('manual', origin + '/manual/offline/de.html');
  const scenario = createScenario({
    currentWindow: manualWindow,
    fetchImpl() {
      return Promise.reject(new Error('offline'));
    }
  });
  click(scenario, createLink({
    href: '../../index.php',
    'data-barabeat-return': ''
  }));
  await flushPromises();
  assert.deepEqual(manualWindow.assigned, []);
}

const playerHtml = fs.readFileSync(path.join(projectRoot, 'Audio/player.html'), 'utf8');
assert.doesNotMatch(playerHtml, /player-legal-footer/);
assert.doesNotMatch(playerHtml, /legal\/navigation\.js/);

const indexHtml = fs.readFileSync(path.join(projectRoot, 'index.php'), 'utf8');
assert.equal((indexHtml.match(/class="app-legal-footer view-legal-footer"/g) || []).length, 3);
assert.match(indexHtml, /id="timelinePanel"[\s\S]*class="app-legal-footer view-legal-footer"/);
assert.match(indexHtml, /id="practicePanel"[\s\S]*class="app-legal-footer view-legal-footer"/);

for (const language of ['de', 'en', 'fr', 'es', 'pt']) {
  const manualHtml = fs.readFileSync(path.join(projectRoot, 'manual/offline', language + '.html'), 'utf8');
  assert.match(manualHtml, /id="manualBackLink"[^>]*data-barabeat-return/);
  assert.match(manualHtml, /src="\.\.\/\.\.\/legal\/navigation\.js"/);
}

console.log('Legal navigation checks passed.');
