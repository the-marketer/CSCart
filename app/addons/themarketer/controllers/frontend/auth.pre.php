<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($mode == 'login') {
		unset($_SESSION['tm']);
		$settings_tm = Registry::get('addons.themarketer');
		$enable = $settings_tm['enable_notifications'];
		if($enable == 'Y' && $_REQUEST['user_login'] !=''){
			$email = $_REQUEST['user_login'];
			$uderdata = db_get_array('SELECT * FROM ?:users WHERE email = ?s ',$email);			
			$_SESSION['tm']['user_data']['email'] = $email;
			$_SESSION['tm']['user_data']['firstname'] = $uderdata[0]['firstname'];
			$_SESSION['tm']['user_data']['lastname'] = $uderdata[0]['lastname'];
			$_SESSION['tm']['user_data']['phone'] = $uderdata[0]['phone'];
		} else {
			unset($_SESSION['tm']);
		}
	}
}

if ($mode == 'logout') {
	unset($_SESSION['tm']);
}