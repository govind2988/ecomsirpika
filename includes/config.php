<?php
// includes/config.php

$env = parse_ini_file(__DIR__ . '/../.env');

if ($env && is_array($env)) {
    foreach ($env as $key => $value) {
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

if (!defined('ADMIN_SESSION_TIMEOUT')) {
    define('ADMIN_SESSION_TIMEOUT', $_ENV['ADMIN_SESSION_TIMEOUT'] ?? 24 * 60 * 60);
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? '/ecomsirpika/');
}
	
if (!defined('MERCHANT_UPI')) {
    define('MERCHANT_UPI', $_ENV['MERCHANT_UPI'] ?? '');
}
if (!defined('WHATSAPP_NO')) {
    define('WHATSAPP_NO', $_ENV['WHATSAPP_NO'] ?? '');
}
if (!defined('ORDER_MODE')) {
    define('ORDER_MODE', $_ENV['ORDER_MODE'] ?? 'traditional');
}
if (!defined('BULK_ORDER_QTY')) {
    define('BULK_ORDER_QTY', (int)($_ENV['BULK_ORDER_QTY'] ?? 5));
}
if (!defined('ENABLE_INDEX_BULK_ORDER')) {
    $val = $_ENV['ENABLE_INDEX_BULK_ORDER'] ?? false;
    define('ENABLE_INDEX_BULK_ORDER', filter_var($val, FILTER_VALIDATE_BOOLEAN));
}