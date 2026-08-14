<?php
require_once __DIR__ . '/../PHP/access_control.php';
barabeat_require_access('page');

$postedRows = [];
$postedObject = isset($_POST['myObj']) ? (string) $_POST['myObj'] : '';
if ($postedObject !== '') {
    $decodedRows = json_decode($postedObject, true);
    if (is_array($decodedRows)) {
        $postedRows = $decodedRows;
    }
}

$launchPayload = [
    'playerRows' => $postedRows,
    'embedded' => isset($_POST['embedded']) && (string) $_POST['embedded'] === '1',
    'uiTheme' => isset($_POST['uiTheme']) ? (string) $_POST['uiTheme'] : '',
];
$generatedLaunchKey = 'barabeat-audio-launch-' . bin2hex(random_bytes(12));
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BaraBeat Player</title>
</head>
<body>
  <p>Player wird geöffnet …</p>
  <script>
    (function () {
      'use strict';

      const launchPayload = <?php echo json_encode($launchPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
      const hashParams = new URLSearchParams(window.location.hash.replace(/^#/, ''));
      const launchKey = hashParams.get('launch') || <?php echo json_encode($generatedLaunchKey); ?>;

      if (Array.isArray(launchPayload.playerRows) && launchPayload.playerRows.length > 0) {
        try {
          localStorage.setItem(launchKey, JSON.stringify(launchPayload));
        } catch (error) {
          document.body.textContent = 'Die Playerdaten konnten nicht lokal übergeben werden.';
          return;
        }
      }

      const targetUrl = new URL('player.html', window.location.href);
      targetUrl.search = window.location.search;
      targetUrl.hash = 'launch=' + encodeURIComponent(launchKey);
      window.location.replace(targetUrl.href);
    })();
  </script>
</body>
</html>
