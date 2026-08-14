(function () {
  'use strict';

  function isSameOriginWindow(candidateWindow) {
    try {
      return Boolean(candidateWindow) &&
        !candidateWindow.closed &&
        candidateWindow.location.origin === window.location.origin;
    } catch (error) {
      return false;
    }
  }

  function getHostWindow() {
    if (window.parent !== window && isSameOriginWindow(window.parent)) {
      return window.parent;
    }
    return window;
  }

  function resolveUrl(href) {
    return new URL(href, window.location.href).href;
  }

  function focusOpenerAndClose() {
    if (!isSameOriginWindow(window.opener)) {
      return false;
    }

    let focusWindow = window.opener;
    try {
      if (focusWindow.parent !== focusWindow && isSameOriginWindow(focusWindow.parent)) {
        focusWindow = focusWindow.parent;
      }
    } catch (error) {
      // The opener itself remains the safe same-origin fallback.
    }

    focusWindow.focus();
    window.close();
    return true;
  }

  function probeOnlineTarget(onlineHref, requireOkResponse) {
    return new Promise(function (resolve, reject) {
      const timeoutId = window.setTimeout(function () {
        reject(new Error('Online page check timed out'));
      }, 2000);

      fetch(onlineHref, {
        method: 'HEAD',
        cache: 'no-store',
        credentials: 'same-origin'
      }).then(function (response) {
        if (requireOkResponse !== false && !response.ok) {
          throw new Error('Online page unavailable');
        }
        return onlineHref;
      }).then(resolve, reject).finally(function () {
        window.clearTimeout(timeoutId);
      });
    });
  }

  function openInternalWindow(href, targetName) {
    const hostWindow = getHostWindow();
    const openedWindow = hostWindow.open(href, targetName || '_blank');
    if (!openedWindow) {
      return null;
    }
    try {
      openedWindow.opener = hostWindow;
    } catch (error) {
      // Browsers may expose a read-only opener; window.open already set it.
    }
    return openedWindow;
  }

  function navigateWindow(targetWindow, href) {
    if (targetWindow && !targetWindow.closed) {
      targetWindow.location.assign(href);
      return;
    }
    window.location.assign(href);
  }

  function openStaticFirst(link, onlineHref) {
    const staticUrl = resolveUrl(link.href);
    const onlineUrl = resolveUrl(onlineHref);
    const targetName = link.getAttribute('target');
    const opensNewWindow = Boolean(targetName && targetName !== '_self');
    const targetWindow = opensNewWindow
      ? openInternalWindow(staticUrl, targetName)
      : null;

    probeOnlineTarget(onlineUrl, true).then(function () {
      navigateWindow(targetWindow, onlineUrl);
    }).catch(function () {
      if (!targetWindow) {
        window.location.assign(staticUrl);
      }
    });
  }

  document.addEventListener('click', function (event) {
    const link = event.target.closest && event.target.closest('a');
    if (!link) {
      return;
    }

    if (link.hasAttribute('data-barabeat-return')) {
      event.preventDefault();
      if (focusOpenerAndClose()) {
        return;
      }
      const returnUrl = resolveUrl(link.href);
      probeOnlineTarget(returnUrl, false).then(function () {
        window.location.assign(returnUrl);
      }).catch(function () {
        // Without a reachable origin window or network, remain on this page.
      });
      return;
    }

    if (link.hasAttribute('data-barabeat-open-window')) {
      event.preventDefault();
      const windowUrl = resolveUrl(link.href);
      if (!openInternalWindow(windowUrl, link.getAttribute('target') || '_blank')) {
        window.location.assign(windowUrl);
      }
      return;
    }

    if (link.hasAttribute('data-online-href')) {
      const onlineHref = link.getAttribute('data-online-href');
      if (!onlineHref) {
        return;
      }
      event.preventDefault();
      openStaticFirst(link, onlineHref);
    }
  });
}());
