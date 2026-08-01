(function () {
  'use strict';

  const statusElId = 'offlineStatus';
  let statusHideTimer = null;

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
      setOfflineStatus('Offline: Lokale Notenblätter und der Audioplayer sind verfügbar.', 'offline', 3500);
    } else {
      const statusEl = document.getElementById(statusElId);
      if (statusEl && statusEl.dataset.state === 'offline') {
        setOfflineStatus('', '');
      }
    }
  }

  function requestOfflinePreparation(registration) {
    if (navigator.onLine === false) {
      return;
    }
    const worker = registration.active || registration.waiting || registration.installing;
    if (!worker) {
      return;
    }
    setOfflineStatus('Offline-Daten werden vorbereitet ...', 'preparing');
    worker.postMessage({ type: 'barabeat-prepare-offline' });
  }

  window.addEventListener('online', function () {
    updateNetworkState();
    navigator.serviceWorker.ready.then(requestOfflinePreparation).catch(function () {});
  });
  window.addEventListener('offline', updateNetworkState);

  window.addEventListener('DOMContentLoaded', function () {
    updateNetworkState();

    if (!('serviceWorker' in navigator)) {
      setOfflineStatus('Dieser Browser unterstützt den Offline-Modus nicht.', 'unsupported', 3500);
      return;
    }

    if (!window.isSecureContext) {
      setOfflineStatus('Offline-Installation benötigt HTTPS.', 'unsupported');
      return;
    }

    navigator.serviceWorker.addEventListener('message', function (event) {
      const message = event.data || {};
      if (message.type === 'barabeat-offline-ready') {
        if (navigator.onLine === false) {
          updateNetworkState();
        } else {
          setOfflineStatus('Offline bereit.', 'ready', 3500);
        }
      } else if (message.type === 'barabeat-offline-error') {
        setOfflineStatus('Offline-Daten konnten nicht vollständig vorbereitet werden.', 'error');
      }
    });

    navigator.serviceWorker.register('service-worker.js', {
      scope: './',
      updateViaCache: 'none'
    }).then(function (registration) {
      return navigator.serviceWorker.ready.then(function () {
        requestOfflinePreparation(registration);
      });
    }).catch(function (error) {
      console.warn('Offline-Modus konnte nicht registriert werden', error);
      setOfflineStatus('Offline-Modus konnte nicht eingerichtet werden.', 'error');
    });
  });
})();
