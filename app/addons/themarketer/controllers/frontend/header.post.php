<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$view = Tygh::$app['view'];

if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {

	//get order info
    $order_id = (int)$_REQUEST['order_id'];
    $order_info = fn_get_order_info($order_id);
	//fn_set_notification('W', __('notice'), var_export($order_info,true));
	
	//get settings
	$settings_tm = Registry::get('addons.themarketer');
	$api_url = 'https://t.themarketer.com/api/v1/save_order';
	$apiKey = $settings_tm['tm_rest_key'];
	$customerId = $settings_tm['tm_customer_id'];
	//get order's products
	$product_data = array();
	foreach($order_info['products'] as $product){
		$product_data[] = [
			"product_id" => $product['product_id'],
            "price" => round($product['price'],2),
            "quantity" => $product['amount'],
            "variation_sku" => $product['product_code']
		];
	}
		
	if($order_info['s_firstname'] !=''){
		$firstname = $order_info['s_firstname'];
	} else {
		$firstname = $order_info['b_firstname'];
	}
	if($order_info['s_lastname'] !=''){
		$lastname = $order_info['s_lastname'];
	} else {
		$lastname = $order_info['b_lastname'];
	}
	if(empty($firstname)){
		$firstname = $order_info['firstname'];
	} else {
		$firstname = $firstname;
	}
	if(empty($lastname)){
		$lastname = $order_info['lastname'];
	} else {
		$lastname = $lastname;
	}
	$email = $order_info['email'];
	if($order_info['s_phone'] !=''){
		$phone = $order_info['s_phone'];
	} else {
		$phone = $order_info['phone'];			
	}
	if($order_info['s_country'] !=''){
		$country = $order_info['s_country'];
	} else {
		$country = $order_info['country'];			
	}
	if($order_info['s_city'] !=''){
		$city = $order_info['s_city'];
	} else {
		$city = $order_info['city'];			
	}
	if($order_info['s_address'] !=''){
		$address= $order_info['s_address'];
	} else {
		$address = $order_info['address'];			
	}
	$discount_code = '';
	if(isset($order_info['coupons'])){
		foreach($order_info['coupons'] as $key=>$value)
		{
		  $coupon = $key;
		}	
		if(!empty($coupon)){
			$discount_code = $coupon;
		} else {
			$discount_code = '';
		}
	}
	if(empty($lastname)){
		$lastname = 'NA';
	} else {
		$lastname = $lastname;
	}
	if(empty($phone)){
		$phone = 'NA';
	} else {
		$phone = $phone;
	}	
}