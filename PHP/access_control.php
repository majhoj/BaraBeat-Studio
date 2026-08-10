<?php

function barabeat_access_config()
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $config = [
        'enabled' => true,
        'password_hash' => '',
        'session_name' => 'barabeat_access',
        'session_lifetime' => 60 * 60 * 24 * 30,
        'idle_timeout' => 60 * 60 * 24 * 30,
    ];

    $localConfigPath = __DIR__ . '/access_config.local.php';
    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (is_array($localConfig)) {
            $config = array_merge($config, $localConfig);
        }
    }

    $environmentHash = getenv('BARABEAT_ACCESS_PASSWORD_HASH');
    if (is_string($environmentHash) && trim($environmentHash) !== '') {
        $config['password_hash'] = trim($environmentHash);
    }

    return $config;
}

function barabeat_access_is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    return isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
}

function barabeat_access_base_path()
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    foreach (['/PHP/', '/Audio/'] as $marker) {
        $markerPosition = strpos($scriptName, $marker);
        if ($markerPosition !== false) {
            return rtrim(substr($scriptName, 0, $markerPosition), '/') . '/';
        }
    }

    $directory = str_replace('\\', '/', dirname($scriptName));
    return ($directory === '/' ? '/' : rtrim($directory, '/') . '/');
}

function barabeat_access_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = barabeat_access_config();
    $sessionLifetime = max(3600, (int) ($config['session_lifetime'] ?? 0));

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name((string) ($config['session_name'] ?? 'barabeat_access'));
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => barabeat_access_base_path(),
        'secure' => barabeat_access_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function barabeat_access_password_version($passwordHash)
{
    return hash('sha256', (string) $passwordHash);
}

function barabeat_access_is_authenticated()
{
    $config = barabeat_access_config();
    if (empty($config['enabled'])) {
        return true;
    }

    barabeat_access_start_session();

    $passwordHash = (string) ($config['password_hash'] ?? '');
    $expectedVersion = barabeat_access_password_version($passwordHash);
    $authenticatedVersion = (string) ($_SESSION['barabeat_access_version'] ?? '');
    $lastSeen = (int) ($_SESSION['barabeat_access_last_seen'] ?? 0);
    $idleTimeout = max(3600, (int) ($config['idle_timeout'] ?? 0));

    if ($authenticatedVersion === '' || !hash_equals($expectedVersion, $authenticatedVersion)) {
        return false;
    }
    if ($lastSeen <= 0 || time() - $lastSeen > $idleTimeout) {
        unset($_SESSION['barabeat_access_version'], $_SESSION['barabeat_access_last_seen']);
        return false;
    }

    $_SESSION['barabeat_access_last_seen'] = time();
    return true;
}

function barabeat_access_csrf_token()
{
    barabeat_access_start_session();
    if (empty($_SESSION['barabeat_access_csrf'])) {
        $_SESSION['barabeat_access_csrf'] = bin2hex(random_bytes(24));
    }
    return (string) $_SESSION['barabeat_access_csrf'];
}

function barabeat_access_logout()
{
    barabeat_access_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParams = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => !empty($cookieParams['secure']),
            'httponly' => !empty($cookieParams['httponly']),
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function barabeat_access_handle_login()
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['barabeat_login'] ?? '') !== '1') {
        return '';
    }

    $config = barabeat_access_config();
    barabeat_access_start_session();

    $lockUntil = (int) ($_SESSION['barabeat_access_lock_until'] ?? 0);
    if ($lockUntil > time()) {
        return 'Zu viele Versuche. Bitte warte noch einen Moment.';
    }

    $csrfToken = (string) ($_POST['barabeat_csrf'] ?? '');
    if ($csrfToken === '' || !hash_equals(barabeat_access_csrf_token(), $csrfToken)) {
        return 'Die Anmeldung ist abgelaufen. Bitte versuche es erneut.';
    }

    $passwordHash = (string) ($config['password_hash'] ?? '');
    $password = (string) ($_POST['barabeat_password'] ?? '');
    if ($passwordHash !== '' && password_verify($password, $passwordHash)) {
        session_regenerate_id(false);
        $_SESSION['barabeat_access_version'] = barabeat_access_password_version($passwordHash);
        $_SESSION['barabeat_access_last_seen'] = time();
        $_SESSION['barabeat_access_failures'] = 0;
        unset($_SESSION['barabeat_access_lock_until']);

        header('Location: ' . barabeat_access_base_path() . 'index.php', true, 303);
        exit;
    }

    usleep(600000);
    $failures = (int) ($_SESSION['barabeat_access_failures'] ?? 0) + 1;
    $_SESSION['barabeat_access_failures'] = $failures;
    if ($failures >= 5) {
        $_SESSION['barabeat_access_lock_until'] = time() + 30;
        $_SESSION['barabeat_access_failures'] = 0;
    }

    return 'Das Zugangspasswort ist nicht richtig.';
}

function barabeat_access_render_login($errorMessage = '', $configurationMissing = false)
{
    http_response_code($configurationMissing ? 503 : 401);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; style-src 'unsafe-inline'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'self'");

    $basePath = barabeat_access_base_path();
    $formAction = htmlspecialchars($basePath . 'index.php', ENT_QUOTES, 'UTF-8');
    $logoPath = htmlspecialchars($basePath . 'Assets/pwa-icon-192.png', ENT_QUOTES, 'UTF-8');
    $safeError = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    $csrfToken = htmlspecialchars(barabeat_access_csrf_token(), ENT_QUOTES, 'UTF-8');
    ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#6a4a2d">
    <title>Zugang | BaraBeat Studio</title>
    <style>
        :root { color-scheme: light; font-family: Arial, Helvetica, sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; color: #3f3023; background: #f0dfc4; }
        main { width: min(100%, 420px); padding: 30px; border: 1px solid #b98d5e; border-radius: 8px; background: #fff9ef; box-shadow: 0 14px 38px rgba(75, 54, 34, 0.18); }
        header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
        img { width: 62px; height: 62px; border-radius: 8px; }
        h1 { margin: 0; font-size: 24px; line-height: 1.15; }
        p { margin: 7px 0 0; color: #765f4b; line-height: 1.45; }
        label { display: block; margin-bottom: 7px; font-weight: 700; }
        input { width: 100%; min-height: 46px; padding: 9px 11px; border: 1px solid #a97c4d; border-radius: 6px; background: #fff; color: #2e241b; font: inherit; }
        input:focus { outline: 3px solid rgba(40, 126, 91, 0.2); border-color: #287e5b; }
        button { width: 100%; min-height: 46px; margin-top: 14px; border: 1px solid #174f39; border-radius: 6px; background: #226b4d; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        button:hover { background: #19583f; }
        .message { margin: 0 0 16px; padding: 10px 12px; border-left: 4px solid #9b3e2f; background: #f8e4df; color: #7d2f23; }
        .config { border-left-color: #a86b17; background: #fff0cf; color: #6c4613; }
        @media (max-width: 520px) { body { padding: 14px; } main { padding: 22px; } }
    </style>
</head>
<body>
    <main>
        <header>
            <img src="<?php echo $logoPath; ?>" alt="BaraBeat Logo">
            <div>
                <h1>BaraBeat Studio</h1>
                <p>Geschützter Zugang</p>
            </div>
        </header>
        <?php if ($configurationMissing): ?>
            <p class="message config">Der Zugang ist noch nicht auf dem Server konfiguriert.</p>
        <?php else: ?>
            <?php if ($safeError !== ''): ?>
                <p class="message" role="alert"><?php echo $safeError; ?></p>
            <?php endif; ?>
            <form method="post" action="<?php echo $formAction; ?>">
                <input type="hidden" name="barabeat_login" value="1">
                <input type="hidden" name="barabeat_csrf" value="<?php echo $csrfToken; ?>">
                <label for="barabeatPassword">Zugangspasswort</label>
                <input id="barabeatPassword" name="barabeat_password" type="password" autocomplete="current-password" required autofocus>
                <button type="submit">Anmelden</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
    <?php
    exit;
}

function barabeat_require_access($responseType = 'page')
{
    $config = barabeat_access_config();
    if (empty($config['enabled'])) {
        return;
    }

    $passwordHash = (string) ($config['password_hash'] ?? '');
    if ($passwordHash === '' || password_get_info($passwordHash)['algo'] === null) {
        if ($responseType === 'page') {
            barabeat_access_render_login('', true);
        }
        http_response_code(503);
        header('Cache-Control: no-store, max-age=0');
        if ($responseType === 'json') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Der Zugang ist nicht konfiguriert.'], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Der Zugang ist nicht konfiguriert.';
        }
        exit;
    }

    if (isset($_GET['barabeat_logout'])) {
        barabeat_access_logout();
        header('Location: ' . barabeat_access_base_path() . 'index.php', true, 303);
        exit;
    }

    $loginError = barabeat_access_handle_login();
    if (barabeat_access_is_authenticated()) {
        return;
    }

    if ($responseType === 'page') {
        barabeat_access_render_login($loginError);
    }

    http_response_code(401);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    if ($responseType === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Anmeldung erforderlich.'], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Anmeldung erforderlich.';
    }
    exit;
}

