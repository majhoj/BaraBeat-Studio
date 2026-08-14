(function () {
  'use strict';

  const statusElId = 'offlineStatus';
  let statusHideTimer = null;
  let preparedWorker = null;

  function t(key) {
    return window.BaraBeatI18n && typeof window.BaraBeatI18n.t === 'function'
      ? window.BaraBeatI18n.t(key)
      : key;
  }

  function setOfflineStatus(message, state, hideAfterMs) {
    const statusEl = document.getElementById(statusElId);
    if (!statusEl) {
      return;
    }
    window.clearTimeout(statusHideTimer);
    statusHideTimer = null;
    statusEl.textContent = message || '';
    statusEl.dataset.state = state || '';
    statusEl.hidden = !message;
    if (message && Number(hideAfterMs) > 0) {
      statusHideTimer = window.setTimeout(function () {
        setOfflineStatus('', '');
      }, Number(hideAfterMs));
    }
  }

  function updateNetworkState() {
    const isOnline = navigator.onLine !== false;
    document.body.dataset.networkStatus = isOnline ? 'online' : 'offline';
    if (!isOnline) {
      setOfflineStatus(t('offline.available'), 'offline', 3500);
    } else {
      const statusEl = document.getElementById(statusElId);
      if (statusEl && statusEl.dataset.state === 'offline') {
        setOfflineStatus('', '');
      }
    }
  }

  function requestOfflinePreparation(registration, force) {
    if (navigator.onLine === false) {
      return;
    }
    const worker = navigator.serviceWorker.controller || registration.active;
    if (!worker) {
      return;
    }
    if (!force && preparedWorker === worker) {
      return;
    }
    preparedWorker = worker;
    setOfflineStatus(t('offline.preparing'), 'preparing');
    worker.postMessage({ type: 'barabeat-prepare-offline' });
  }

  window.addEventListener('online', function () {
    updateNetworkState();
    navigator.serviceWorker.ready.then(function (registration) {
      requestOfflinePreparation(registration, true);
    }).catch(function () {});
  });
  window.addEventListener('offline', updateNetworkState);

  window.addEventListener('DOMContentLoaded', function () {
    updateNetworkState();

    if (!('serviceWorker' in navigator)) {
      setOfflineStatus(t('offline.unsupported'), 'unsupported', 3500);
      return;
    }

    if (!window.isSecureContext) {
      setOfflineStatus(t('offline.httpsRequired'), 'unsupported');
      return;
    }

    navigator.serviceWorker.addEventListener('message', function (event) {
      const message = event.data || {};
      if (message.type === 'barabeat-offline-ready') {
        if (navigator.onLine === false) {
          updateNetworkState();
        } else {
          setOfflineStatus(t('offline.ready'), 'ready', 3500);
        }
      } else if (message.type === 'barabeat-offline-error') {
        setOfflineStatus(t('offline.prepareFailed'), 'error');
      }
    });

    navigator.serviceWorker.addEventListener('controllerchange', function () {
      preparedWorker = null;
      navigator.serviceWorker.ready.then(function (registration) {
        requestOfflinePreparation(registration, true);
      }).catch(function () {});
    });

    navigator.serviceWorker.register('service-worker.js', {
      scope: './',
      updateViaCache: 'none'
    }).then(function (registration) {
      return navigator.serviceWorker.ready.then(function (readyRegistration) {
        requestOfflinePreparation(readyRegistration || registration);
      });
    }).catch(function (error) {
      console.warn('Offline-Modus konnte nicht registriert werden', error);
      setOfflineStatus(t('offline.setupFailed'), 'error');
    });
  });
})();
