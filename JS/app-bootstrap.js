(function (global) {
  'use strict';

  const EDITION_STORAGE_KEY = 'barabeat-offline-edition-config-v1';

  function isEditionConfig(value) {
    return Boolean(value &&
      typeof value === 'object' &&
      typeof value.edition === 'string' &&
      value.edition &&
      value.features &&
      typeof value.features === 'object');
  }

  function cloneConfig(value) {
    try {
      return JSON.parse(JSON.stringify(value));
    } catch (error) {
      return null;
    }
  }

  function rememberEditionConfig(config) {
    if (global.BARABEAT_OFFLINE_BOOT === true || !isEditionConfig(config)) {
      return false;
    }
    try {
      global.localStorage.setItem(EDITION_STORAGE_KEY, JSON.stringify(config));
      return true;
    } catch (error) {
      return false;
    }
  }

  function getOfflineEditionConfig(fallbackConfig) {
    try {
      const storedConfig = JSON.parse(global.localStorage.getItem(EDITION_STORAGE_KEY) || 'null');
      if (isEditionConfig(storedConfig)) {
        return storedConfig;
      }
    } catch (error) {
      // A restrictive generated fallback is used when storage is unavailable.
    }
    return cloneConfig(fallbackConfig) || {
      edition: 'demo',
      features: {},
      content: {},
      messages: {},
      debug: false
    };
  }

  function applyOfflineBootUi() {
    if (global.BARABEAT_OFFLINE_BOOT !== true || !global.document) {
      return;
    }
    global.document.body.dataset.offlineBoot = 'true';
    global.document.querySelectorAll('[data-online-only]').forEach(function (element) {
      element.hidden = true;
      element.setAttribute('aria-hidden', 'true');
      if ('disabled' in element) {
        element.disabled = true;
      }
    });
  }

  global.BaraBeatAppBootstrap = Object.freeze({
    editionStorageKey: EDITION_STORAGE_KEY,
    isOfflineBoot: function () {
      return global.BARABEAT_OFFLINE_BOOT === true;
    },
    rememberEditionConfig: rememberEditionConfig,
    getOfflineEditionConfig: getOfflineEditionConfig,
    applyOfflineBootUi: applyOfflineBootUi
  });

  if (global.document) {
    if (global.document.readyState === 'loading') {
      global.document.addEventListener('DOMContentLoaded', applyOfflineBootUi, { once: true });
    } else {
      applyOfflineBootUi();
    }
  }
})(window);
