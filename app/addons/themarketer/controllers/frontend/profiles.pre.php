<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($mode == 'update' || $mode == 'add') {
		//print_r($_REQUEST);die();
		unset($_SESSION['tm']);
		$settings_tm = Registry::get('addons.themarketer');
		$enable = $settings_tm['enable_notifications'];
		if($enable == 'Y' && isset($_REQUEST['user_data']["email"])){
			$_SESSION['tm'] = $_REQUEST;
			//unsubscribe
			if(!isset($_REQUEST['mailing_lists'])){
				$settings_tm = Registry::get('addons.themarketer');
				$apiURL = 'https://t.themarketer.com/api/v1/remove_subscriber';
				$apiKey = $settings_tm['tm_rest_key'];
				$customerId = $settings_tm['tm_customer_id'];		
				$registerData = [
					"k" => $apiKey,
					"u" => $customerId,
					"email" => $_REQUEST['user_data']["email"], //required
					"phone" => $_REQUEST['user_data']["phone"], //optional
					"name" => $_REQUEST['user_data']["firstname"].' '.$_REQUEST['user_data']["lastname"] //optional
				];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $apiURL);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_TIMEOUT, '30');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($registerData));
				$response = curl_exec($ch);
				curl_close($ch);					
			} else {
				$settings_tm = Registry::get('addons.themarketer');
				$apiURL = 'https://t.themarketer.com/api/v1/add_subscriber';
				$apiKey = $settings_tm['tm_rest_key'];
				$customerId = $settings_tm['tm_customer_id'];		
				$registerData = [
					"k" => $apiKey,
					"u" => $customerId,
					"email" => $_REQUEST['user_data']["email"], //required
					"phone" => $_REQUEST['user_data']["phone"], //optional
					"name" => $_REQUEST['user_data']["firstname"].' '.$_REQUEST['user_data']["lastname"] //optional
				];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $apiURL);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_TIMEOUT, '30');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($registerData));
				$response = curl_exec($ch);
				curl_close($ch);						
			}
		} else {
			unset($_SESSION['tm']);
		}
	}
}
