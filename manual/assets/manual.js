    (function () {
      var backLink = document.getElementById('manualBackLink');
      if (!backLink) {
        return;
      }
      backLink.addEventListener('click', function (event) {
        try {
          var referrer = document.referrer ? new URL(document.referrer) : null;
          if (referrer && referrer.origin === window.location.origin && window.history.length > 1) {
            event.preventDefault();
            window.history.back();
          }
        } catch (error) {
          // Der normale Link zu index.php bleibt als Fallback erhalten.
        }
      });
    }());
