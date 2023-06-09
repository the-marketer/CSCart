<?php
use Tygh\Http;
use Tygh\Registry;


if (!defined('BOOTSTRAP')) { die('Access denied'); }

//report on status change
function fn_themarketer_change_order_status(&$statusTo, &$statusFrom, &$orderInfo)
{
	//get settings
	$settings_tm = Registry::get('addons.themarketer');
	$order_id = $orderInfo['order_id'];
	$apiURL = 'https://t.themarketer.com/api/v1/update_order_status';
	$apiKey = $settings_tm['tm_rest_key'];
	$customerId = $settings_tm['tm_customer_id'];
	$new_status_name = db_get_field('SELECT ?:status_descriptions.description as status_name from ?:status_descriptions,?:statuses WHERE ?:statuses.status_id= ?:status_descriptions.status_id AND ?:statuses.status="'.$statusTo.'" AND ?:statuses.type="O" AND ?:status_descriptions.lang_code = ?s ',CART_LANGUAGE);
	//create array
	$orderStatus = array(
		"k" => "$apiKey",
		"u" => "$customerId",
		"order_number" => "$order_id", 
		"order_status" => "$new_status_name" 
	);
	//send request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiURL."?".http_build_query($orderStatus));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_TIMEOUT, '30');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);		
	$response = curl_exec($ch);	
	curl_close($ch);
		
}