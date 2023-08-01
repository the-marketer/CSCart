<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$view = Tygh::$app['view'];

if ($mode == 'search') {
	
	Registry::get('view')->assign('tm_search', $_REQUEST['q']);	
	//fn_set_notification('W', __('notice'), var_export($_REQUEST['q'],true));
	
}