<?php

$d = explode('/', __DIR__ . (substr(__DIR__, -1) === '/' ? '' : '/'));
$root = implode('/', array_splice($d, 0, -4));

if (!defined('AREA')) {
    define('AREA', 'A');
}
if (!defined('ACCOUNT_TYPE')) {
    define('ACCOUNT_TYPE', 'admin');
}
if (!defined('CONSOLE')) {
    define('CONSOLE', true);
}

if (!defined('NO_SESSION')) {
    define('NO_SESSION', true);
}
if (!defined('MKTR_CRON')) {
    define('MKTR_CRON', true);
}

if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}
if (!isset($_SERVER['REMOTE_ADDR'])) {
    if (isset($_SERVER['SERVER_ADDR'])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['SERVER_ADDR'];
    } else {
        $_SERVER['REMOTE_ADDR'] = '185.134.113.119';
    }
}

try {
    $_REQUEST['allow_initialization'] = '';
    $_REQUEST['switch_company_id'] = 'all';

    require_once $root . '/init.php';
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
    @ini_set('memory_limit', '4086G');
    @ini_set('max_execution_time', '3600');
    @set_time_limit(3600);
    @ini_set('zlib.output_compression', 0);
    fn_dispatch();
    // fn_dispatch('mktr', 'api', 'cron', '', AREA);
    // fn_dispatch('mktr', 'cron', 'index', '', AREA);
} catch (Tygh\Exceptions\AException $e) {
    $e->output();
}
