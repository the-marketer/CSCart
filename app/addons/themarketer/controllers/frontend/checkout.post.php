<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$view = Tygh::$app['view'];

if ($mode == 'complete' && !empty($_REQUEST['order_id'])) {

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
   $orderData = [
            "k" => $apiKey,
            "u" => $customerId,
            "number" => "$order_id", 
            "email_address" => "$email",
            "phone" => "$phone",
            "firstname" => "$firstname",
            "lastname" => "$lastname",
            "city" => "$city",
            "county" => "$country",
            "address" => "$address",
            "discount_value" => $order_info['subtotal_discount'], 
            "discount_code" => "$discount_code",
            "shipping" => $order_info['shipping_cost'],
            "tax" => $order_info['tax_subtotal'], 
            "total_value" => $order_info['total'],
            "products" => $product_data
    ];	
	//fn_set_notification('W', __('notice'), var_export($orderData,true));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_TIMEOUT, '30');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($orderData));
	$response = curl_exec($ch);	
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);	
	
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
	$datalayer = '
   dataLayer.push({
        event: "__sm__order",
        number: "'.$order_id.'", //or text "XYZ1234" 
        email_address: "'.$email.'",
        phone: "'.$phone.'",
        firstname: "'.$firstname.'",
        lastname: "'.$lastname.'",
        city: "'.$city.'",
        county: "'.$country.'",
        address: "'.$address.'",
        discount_value: '.$order_info['subtotal_discount'].', // Final value of the discount, such as 10 EUR, without currency
        discount_code: "'.$discount_code.'", //If no discount code is used by the customer, set empty string
        shipping: '.$order_info['shipping_cost'].',
        tax: 20, //final value of taxes (VAT), such as 20 EUR, without currency,
        total_value: '.$order_info['total'].',
        products: '.json_encode($product_data).'
   });
';
	Registry::get('view')->assign('order_datalayer', $datalayer);
	//newletter api
		$settings_tm = Registry::get('addons.themarketer');
		$enable = $settings_tm['enable_notifications'];
		$nl_email = db_get_field('SELECT email FROM ?:subscribers WHERE email= ?s',$order_info["email"]);
		if($enable == 'Y' && $order_info["email"] == $nl_email){
				$settings_tm = Registry::get('addons.themarketer');
				$apiURL = 'https://t.themarketer.com/api/v1/add_subscriber';
				$apiKey = $settings_tm['tm_rest_key'];
				$customerId = $settings_tm['tm_customer_id'];		
				$registerData = [
					"k" => $apiKey,
					"u" => $customerId,
					"email" => $order_info["email"], //required
					"phone" => $order_info["phone"], //optional
					"name" => $order_info["firstname"].' '.$order_info["lastname"] //optional
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
			unset($_SESSION['tm']);
		}	
}