    (function () {
      var languageSelect = document.getElementById('manualLanguageSelect');
      if (!languageSelect) {
        return;
      }
      languageSelect.addEventListener('change', function () {
        var option = languageSelect.options[languageSelect.selectedIndex];
        var language = option ? option.getAttribute('data-language') : '';
        try {
          if (language && window.localStorage) {
            window.localStorage.setItem('barabeat_language', language);
          }
          if (language) {
            var secure = window.location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = 'barabeat_language=' + encodeURIComponent(language) +
              '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
          }
        } catch (error) {
          // The server-side cookie remains the persistence fallback.
        }
        window.location.href = languageSelect.value + (window.location.hash || '');
      });
    }());
