<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

// Editionshinweise werden erst in einer späteren, ausdrücklich freigegebenen Phase gerendert.
