import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const manualDirectory = path.join(projectRoot, 'manual/offline');
const languages = ['de', 'en', 'fr', 'es', 'pt'];

function readAttributes(tag) {
  const attributes = {};
  for (const match of tag.matchAll(/([\w-]+)(?:="([^"]*)")?/g)) {
    attributes[match[1]] = typeof match[2] === 'string' ? match[2] : '';
  }
  return attributes;
}

function findLink(html, label) {
  const tags = html.match(/<a\b[^>]*>[^<]*<\/a>/g) || [];
  const tag = tags.find(function (candidate) {
    return candidate.replace(/<[^>]+>/g, '').trim() === label;
  });
  assert.ok(tag, label + '-Link fehlt');
  return readAttributes(tag);
}

function resolveProjectFile(documentPath, href) {
  const pathWithoutQuery = href.split(/[?#]/, 1)[0];
  return path.resolve(path.dirname(documentPath), pathWithoutQuery);
}

function assertLegalLinks(html, expected) {
  const imprint = findLink(html, 'Impressum');
  const privacy = findLink(html, 'Datenschutz');

  assert.equal(imprint.href, expected.imprintHref);
  assert.equal(privacy.href, expected.privacyHref);
  assert.equal(imprint['data-online-href'], expected.imprintOnlineHref);
  assert.equal(privacy['data-online-href'], expected.privacyOnlineHref);
  assert.equal(imprint.target, '_self');
  assert.equal(privacy.target, '_self');
  assert.doesNotMatch(imprint.href, /\.php(?:$|[?#])/);
  assert.doesNotMatch(privacy.href, /\.php(?:$|[?#])/);

  return { imprint, privacy };
}

for (const language of languages) {
  const manualPath = path.join(manualDirectory, language + '.html');
  const html = fs.readFileSync(manualPath, 'utf8');
  const { imprint, privacy } = assertLegalLinks(html, {
    imprintHref: '../../legal/offline/impressum.html',
    privacyHref: '../../legal/offline/datenschutz.html',
    imprintOnlineHref: '../../impressum.php',
    privacyOnlineHref: '../../datenschutz.php'
  });
  assert.ok(fs.existsSync(resolveProjectFile(manualPath, imprint.href)), 'Impressum-Datei fehlt');
  assert.ok(fs.existsSync(resolveProjectFile(manualPath, privacy.href)), 'Datenschutz-Datei fehlt');

  const navigationScript = (html.match(/<script\b[^>]*src="([^"]*legal\/navigation\.js)"[^>]*><\/script>/) || [])[1];
  assert.equal(navigationScript, '../../legal/navigation.js');
  assert.ok(fs.existsSync(resolveProjectFile(manualPath, navigationScript)), 'Navigation-Skript fehlt');
  assert.doesNotMatch(html, /history\.(?:back|go)\s*\(/);

  console.log('manual/offline/' + language + '.html -> Impressum OK / Datenschutz OK');
}

const dynamicHtml = execFileSync('php', ['-r', [
  "define('BARABEAT_MANUAL_RENDER', true);",
  "$manualLanguage = 'de';",
  "$manualAssetBaseUrl = 'manual/assets';",
  "$manualBackUrl = 'index.php';",
  "$manualLegalBaseUrl = '';",
  "$manualLegalOfflineBaseUrl = 'legal/offline/';",
  "require 'manual/index.php';"
].join(' ')], {
  cwd: projectRoot,
  encoding: 'utf8'
});

assertLegalLinks(dynamicHtml, {
  imprintHref: 'legal/offline/impressum.html',
  privacyHref: 'legal/offline/datenschutz.html',
  imprintOnlineHref: 'impressum.php',
  privacyOnlineHref: 'datenschutz.php'
});
console.log('Bedienungsanleitung.php-Renderpfad -> Impressum OK / Datenschutz OK');
