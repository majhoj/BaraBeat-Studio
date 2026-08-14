<?php
require_once __DIR__ . '/access_control.php';
barabeat_require_access('text');

http_response_code(410);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: text/plain; charset=UTF-8');
echo 'Legacy endpoint disabled.';
