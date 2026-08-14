<?php

require_once __DIR__ . '/i18n.php';

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
        'session_lifetime' => 60 * 60 * 24 * 365 * 5,
        'idle_timeout' => 0,
        'temporary_access_duration' => 5 * 60,
        'temporary_access_state_file' => dirname(__DIR__) . '/Noten/.barabeat_access_window.php',
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

    $environmentWindowFile = getenv('BARABEAT_ACCESS_WINDOW_FILE');
    if (is_string($environmentWindowFile) && trim($environmentWindowFile) !== '') {
        $config['temporary_access_state_file'] = trim($environmentWindowFile);
    }

    return $config;
}

function barabeat_access_window_state_file()
{
    $config = barabeat_access_config();
    return (string) ($config['temporary_access_state_file'] ?? '');
}

function barabeat_access_window_until()
{
    $stateFile = barabeat_access_window_state_file();
    if ($stateFile === '' || !is_file($stateFile)) {
        return 0;
    }

    $stateContent = @file_get_contents($stateFile);
    if (!is_string($stateContent) || !preg_match('/BARABEAT_ACCESS_UNTIL=(\d+)/', $stateContent, $matches)) {
        return 0;
    }

    $accessUntil = (int) $matches[1];
    if ($accessUntil <= time()) {
        @unlink($stateFile);
        return 0;
    }

    return $accessUntil;
}

function barabeat_access_window_is_open()
{
    return barabeat_access_window_until() > time();
}

function barabeat_access_open_window($durationSeconds = null)
{
    $config = barabeat_access_config();
    $configuredDuration = (int) ($config['temporary_access_duration'] ?? 300);
    $duration = $durationSeconds === null ? $configuredDuration : (int) $durationSeconds;
    $duration = max(60, min(30 * 60, $duration));
    $stateFile = barabeat_access_window_state_file();
    if ($stateFile === '' || !is_dir(dirname($stateFile))) {
        return 0;
    }

    $accessUntil = time() + $duration;
    $stateContent = "<?php\nhttp_response_code(404);\nexit;\n/* BARABEAT_ACCESS_UNTIL=" . $accessUntil . " */\n";
    if (@file_put_contents($stateFile, $stateContent, LOCK_EX) === false) {
        return 0;
    }

    @chmod($stateFile, 0600);
    clearstatcache(true, $stateFile);
    return $accessUntil;
}

function barabeat_access_close_window()
{
    $stateFile = barabeat_access_window_state_file();
    if ($stateFile === '' || !is_file($stateFile)) {
        return true;
    }

    $closed = @unlink($stateFile);
    clearstatcache(true, $stateFile);
    return $closed;
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

function barabeat_access_set_cookie($name, $value, $expires, array $cookieParams)
{
    if (PHP_VERSION_ID >= 70300) {
        return setcookie($name, $value, [
            'expires' => $expires,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => !empty($cookieParams['secure']),
            'httponly' => !empty($cookieParams['httponly']),
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);
    }

    return setcookie(
        $name,
        $value,
        $expires,
        $cookieParams['path'] ?? '/',
        $cookieParams['domain'] ?? '',
        !empty($cookieParams['secure']),
        !empty($cookieParams['httponly'])
    );
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
    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
    session_name((string) ($config['session_name'] ?? 'barabeat_access'));
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => $sessionLifetime,
            'path' => barabeat_access_base_path(),
            'secure' => barabeat_access_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        session_set_cookie_params(
            $sessionLifetime,
            barabeat_access_base_path(),
            '',
            barabeat_access_is_https(),
            true
        );
    }
    session_start();
}

function barabeat_access_refresh_session_cookie()
{
    if (session_status() !== PHP_SESSION_ACTIVE || !ini_get('session.use_cookies')) {
        return;
    }

    $config = barabeat_access_config();
    $sessionLifetime = max(3600, (int) ($config['session_lifetime'] ?? 0));
    $cookieParams = session_get_cookie_params();
    $cookieParams['samesite'] = $cookieParams['samesite'] ?? 'Lax';
    barabeat_access_set_cookie(
        session_name(),
        session_id(),
        time() + $sessionLifetime,
        $cookieParams
    );
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
    $idleTimeout = (int) ($config['idle_timeout'] ?? 0);

    if ($authenticatedVersion === '' || !hash_equals($expectedVersion, $authenticatedVersion)) {
        return false;
    }
    if ($lastSeen <= 0 || ($idleTimeout > 0 && time() - $lastSeen > $idleTimeout)) {
        unset($_SESSION['barabeat_access_version'], $_SESSION['barabeat_access_last_seen']);
        return false;
    }

    $_SESSION['barabeat_access_last_seen'] = time();
    barabeat_access_refresh_session_cookie();
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

function barabeat_access_write_error($responseType, $statusCode, $message)
{
    http_response_code($statusCode);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    if ($responseType === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
    exit;
}

function barabeat_require_write_csrf($responseType = 'json')
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        barabeat_access_write_error($responseType, 405, barabeat_t('auth.postOnly'));
    }

    $config = barabeat_access_config();
    if (empty($config['enabled']) || barabeat_access_window_is_open()) {
        return true;
    }

    $providedToken = (string) ($_POST['csrfToken'] ?? $_POST['csrf'] ?? '');
    if ($providedToken === '' || !hash_equals(barabeat_access_csrf_token(), $providedToken)) {
        barabeat_access_write_error($responseType, 403, barabeat_t('auth.sessionExpired'));
    }

    return true;
}

function barabeat_access_logout()
{
    barabeat_access_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParams = session_get_cookie_params();
        $cookieParams['samesite'] = $cookieParams['samesite'] ?? 'Lax';
        barabeat_access_set_cookie(
            session_name(),
            '',
            time() - 42000,
            $cookieParams
        );
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
        return barabeat_t('auth.tooManyAttempts');
    }

    $csrfToken = (string) ($_POST['barabeat_csrf'] ?? '');
    if ($csrfToken === '' || !hash_equals(barabeat_access_csrf_token(), $csrfToken)) {
        return barabeat_t('auth.loginExpired');
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

    return barabeat_t('auth.wrongPassword');
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
    $imprintPath = htmlspecialchars($basePath . 'impressum.php', ENT_QUOTES, 'UTF-8');
    $privacyPath = htmlspecialchars($basePath . 'datenschutz.php', ENT_QUOTES, 'UTF-8');
    $imprintOfflinePath = htmlspecialchars($basePath . 'legal/offline/impressum.html', ENT_QUOTES, 'UTF-8');
    $privacyOfflinePath = htmlspecialchars($basePath . 'legal/offline/datenschutz.html', ENT_QUOTES, 'UTF-8');
    $legalNavigationPath = htmlspecialchars($basePath . 'legal/navigation.js', ENT_QUOTES, 'UTF-8');
    $safeError = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    $csrfToken = htmlspecialchars(barabeat_access_csrf_token(), ENT_QUOTES, 'UTF-8');
    ?>
<!doctype html>
<html lang="<?php echo htmlspecialchars(barabeat_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#6a4a2d">
    <title><?php echo htmlspecialchars(barabeat_t('auth.pageTitle'), ENT_QUOTES, 'UTF-8'); ?></title>
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
        .legal-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px 10px; margin-top: 22px; padding-top: 16px; border-top: 1px solid #dbc3a6; color: #765f4b; font-size: 14px; }
        .legal-links a { color: #5f3d22; text-underline-offset: 2px; }
        .legal-links a:focus-visible { outline: 3px solid rgba(40, 126, 91, 0.35); outline-offset: 3px; border-radius: 2px; }
        @media (max-width: 520px), (hover: none) and (pointer: coarse) {
            body { padding: 14px; }
            main { padding: 22px; }
            input, select, textarea { font-size: 16px; }
        }
    </style>
</head>
<body>
    <main>
        <header>
            <img src="<?php echo $logoPath; ?>" alt="BaraBeat Logo">
            <div>
                <h1>BaraBeat Studio</h1>
                <p><?php echo htmlspecialchars(barabeat_t('auth.protectedAccess'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </header>
        <?php if ($configurationMissing): ?>
            <p class="message config"><?php echo htmlspecialchars(barabeat_t('auth.configurationMissing'), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php else: ?>
            <?php if ($safeError !== ''): ?>
                <p class="message" role="alert"><?php echo $safeError; ?></p>
            <?php endif; ?>
            <form method="post" action="<?php echo $formAction; ?>">
                <input type="hidden" name="barabeat_login" value="1">
                <input type="hidden" name="barabeat_csrf" value="<?php echo $csrfToken; ?>">
                <label for="barabeatPassword"><?php echo htmlspecialchars(barabeat_t('auth.password'), ENT_QUOTES, 'UTF-8'); ?></label>
                <input id="barabeatPassword" name="barabeat_password" type="password" autocomplete="current-password" required autofocus>
                <button type="submit"><?php echo htmlspecialchars(barabeat_t('auth.signIn'), ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        <?php endif; ?>
        <nav class="legal-links" aria-label="Rechtliche Informationen">
            <span class="access-copyright">© 2020–<?php echo date('Y'); ?> Art &amp; Werbeteam GmbH · BaraBeat</span>
            <span aria-hidden="true">·</span>
            <a href="<?php echo $imprintOfflinePath; ?>" target="_blank" rel="opener" data-online-href="<?php echo $imprintPath; ?>">Impressum</a>
            <span aria-hidden="true">·</span>
            <a href="<?php echo $privacyOfflinePath; ?>" target="_blank" rel="opener" data-online-href="<?php echo $privacyPath; ?>">Datenschutz</a>
        </nav>
    </main>
    <script src="<?php echo $legalNavigationPath; ?>"></script>
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
            echo json_encode(['success' => false, 'message' => barabeat_t('auth.notConfigured')], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo barabeat_t('auth.notConfigured');
        }
        exit;
    }

    if (isset($_GET['barabeat_logout'])) {
        barabeat_access_logout();
        header('Location: ' . barabeat_access_base_path() . 'index.php', true, 303);
        exit;
    }

    if (barabeat_access_window_is_open()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Robots-Tag: noindex, nofollow, noarchive');
        header('X-BaraBeat-Temporary-Access: active');
        return;
    }

    $loginError = barabeat_access_handle_login();
    if (barabeat_access_is_authenticated()) {
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        return;
    }

    if ($responseType === 'page') {
        barabeat_access_render_login($loginError);
    }

    http_response_code(401);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    if ($responseType === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => barabeat_t('auth.required')], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo barabeat_t('auth.required');
    }
    exit;
}
