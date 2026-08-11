(function (global) {
  'use strict';

  const config = global.BaraBeatI18nConfig && typeof global.BaraBeatI18nConfig === 'object'
    ? global.BaraBeatI18nConfig
    : {};
  const fallbackLanguage = typeof config.fallbackLanguage === 'string' ? config.fallbackLanguage : 'en';
  const cookieName = typeof config.cookieName === 'string' ? config.cookieName : 'barabeat_language';
  const storageKey = typeof config.storageKey === 'string' ? config.storageKey : cookieName;
  const supportedLanguages = Array.isArray(config.supportedLanguages)
    ? config.supportedLanguages.slice()
    : ['de', 'en', 'fr', 'es', 'pt'];
  const catalogs = config.catalogs && typeof config.catalogs === 'object' ? config.catalogs : {};
  const availableLanguages = Array.isArray(config.availableLanguages)
    ? config.availableLanguages.filter(function (language) { return catalogs[language]; })
    : Object.keys(catalogs);
  const locales = config.locales && typeof config.locales === 'object' ? config.locales : {};
  const warnedKeys = Object.create(null);

  function normalizeLanguage(language) {
    const value = String(language || '').trim().toLowerCase().replace(/_/g, '-');
    if (!value) {
      return null;
    }
    const primary = value.split('-')[0];
    return supportedLanguages.indexOf(primary) !== -1 ? primary : fallbackLanguage;
  }

  function resolveLanguage(language) {
    const normalized = normalizeLanguage(language) || fallbackLanguage;
    return availableLanguages.indexOf(normalized) !== -1 ? normalized : fallbackLanguage;
  }

  function readStoredLanguage() {
    try {
      return global.localStorage ? global.localStorage.getItem(storageKey) : null;
    } catch (error) {
      return null;
    }
  }

  function readCookieLanguage() {
    const prefix = encodeURIComponent(cookieName) + '=';
    const parts = String(global.document && global.document.cookie || '').split(';');
    for (let index = 0; index < parts.length; index += 1) {
      const part = parts[index].trim();
      if (part.indexOf(prefix) === 0) {
        return decodeURIComponent(part.slice(prefix.length));
      }
    }
    return null;
  }

  function detectBrowserLanguage() {
    const values = global.navigator && Array.isArray(global.navigator.languages)
      ? global.navigator.languages
      : [global.navigator && global.navigator.language];
    for (let index = 0; index < values.length; index += 1) {
      const normalized = normalizeLanguage(values[index]);
      if (normalized) {
        return normalized;
      }
    }
    return fallbackLanguage;
  }

  let activeLanguage = resolveLanguage(
    readStoredLanguage() ||
    readCookieLanguage() ||
    config.requestedLanguage ||
    config.language ||
    detectBrowserLanguage()
  );

  function storeLanguage(language) {
    try {
      if (global.localStorage) {
        global.localStorage.setItem(storageKey, language);
      }
    } catch (error) {
      // Cookie persistence remains available when localStorage is blocked.
    }

    if (global.document) {
      const secure = global.location && global.location.protocol === 'https:' ? '; Secure' : '';
      global.document.cookie = encodeURIComponent(cookieName) + '=' + encodeURIComponent(language) +
        '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
    }
  }

  function findValue(catalog, key) {
    const segments = String(key).split('.');
    let value = catalog;
    for (let index = 0; index < segments.length; index += 1) {
      if (!value || typeof value !== 'object' || !Object.prototype.hasOwnProperty.call(value, segments[index])) {
        return null;
      }
      value = value[segments[index]];
    }
    return typeof value === 'string' || typeof value === 'number' ? String(value) : null;
  }

  function warnOnce(code, message) {
    if (warnedKeys[code]) {
      return;
    }
    warnedKeys[code] = true;
    if (global.console && typeof global.console.warn === 'function') {
      global.console.warn(message);
    }
  }

  function interpolate(text, values) {
    const replacements = values && typeof values === 'object' ? values : {};
    return String(text).replace(/\{([^{}]+)\}/g, function (match, name) {
      return Object.prototype.hasOwnProperty.call(replacements, name) ? String(replacements[name]) : match;
    });
  }

  function t(key, values) {
    let text = findValue(catalogs[activeLanguage], key);
    if (text === null && activeLanguage !== fallbackLanguage) {
      warnOnce(activeLanguage + ':' + key, '[BaraBeat i18n] Missing key "' + key +
        '" in "' + activeLanguage + '"; using "' + fallbackLanguage + '".');
      text = findValue(catalogs[fallbackLanguage], key);
    }
    if (text === null) {
      warnOnce('missing:' + key, '[BaraBeat i18n] Missing translation key: ' + key);
      text = String(key);
    }
    return interpolate(text, values);
  }

  function tp(key, count, values) {
    let category = 'other';
    if (typeof Intl === 'object' && typeof Intl.PluralRules === 'function') {
      category = new Intl.PluralRules(getLocale()).select(Number(count));
    }
    const mergedValues = Object.assign({ count: count }, values || {});
    return t(key + '.' + category, mergedValues);
  }

  function getLanguage() {
    return activeLanguage;
  }

  function getLocale() {
    return locales[activeLanguage] || locales[fallbackLanguage] || 'en-GB';
  }

  function setLanguage(language) {
    activeLanguage = resolveLanguage(language);
    storeLanguage(activeLanguage);
    if (global.location && typeof global.location.reload === 'function') {
      global.location.reload();
    }
  }

  function applyTranslations(root) {
    const scope = root && typeof root.querySelectorAll === 'function' ? root : global.document;
    if (!scope) {
      return;
    }
    scope.querySelectorAll('[data-i18n]').forEach(function (element) {
      element.textContent = t(element.getAttribute('data-i18n'));
    });
    scope.querySelectorAll('[data-i18n-aria-label]').forEach(function (element) {
      element.setAttribute('aria-label', t(element.getAttribute('data-i18n-aria-label')));
    });
    scope.querySelectorAll('[data-i18n-title]').forEach(function (element) {
      element.setAttribute('title', t(element.getAttribute('data-i18n-title')));
    });
    scope.querySelectorAll('[data-i18n-placeholder]').forEach(function (element) {
      element.setAttribute('placeholder', t(element.getAttribute('data-i18n-placeholder')));
    });
    scope.querySelectorAll('[data-barabeat-language-select]').forEach(function (select) {
      select.value = activeLanguage;
      select.addEventListener('change', function () {
        setLanguage(select.value);
      });
    });
  }

  storeLanguage(activeLanguage);
  if (global.document && global.document.documentElement) {
    global.document.documentElement.lang = activeLanguage;
  }

  global.BaraBeatI18n = Object.freeze({
    t: t,
    tp: tp,
    getLanguage: getLanguage,
    setLanguage: setLanguage,
    getLocale: getLocale,
    getSupportedLanguages: function () { return supportedLanguages.slice(); },
    getAvailableLanguages: function () { return availableLanguages.slice(); },
    applyTranslations: applyTranslations
  });

  if (global.document) {
    if (global.document.readyState === 'loading') {
      global.document.addEventListener('DOMContentLoaded', function () {
        applyTranslations(global.document);
      }, { once: true });
    } else {
      applyTranslations(global.document);
    }
  }
})(window);
