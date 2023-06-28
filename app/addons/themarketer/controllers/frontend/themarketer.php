<?php

use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

@set_time_limit(0);
@ini_set('memory_limit', '2048M');

//ORDERS EXPORT
if ($mode == 'orders_export') {
	$settings_tm = Registry::get('addons.themarketer');
	if($settings_tm['tm_rest_key'] !=$_REQUEST['key']){
		die('wrong');
		exit();
	}
	
	//create json file
	$filename = Registry::get('config.dir.var') . 'files/themarketer/orders_export_page_'.$_REQUEST['page'].'.json';
	$jsonfile = fopen($filename, "w");
	
	//get orders
	$items_per_page = 50;
	$timeperiodstart = date('m/d/Y',strtotime($_REQUEST['start_date']));
	$params = array('page'=>$_REQUEST['page'],'period'=>'C','time_from'=>$timeperiodstart,'time_to'=>date('m/d/Y'));
	$orders = fn_get_orders($params,$items_per_page);
	$ordersarr = array();
	foreach($orders[0] as $k=>$order){
		$orderId = $order['order_id'];
		$date_created = date('Y-m-d G:i',$order['timestamp']);
		$order_info = fn_get_order_info($orderId);//order data
		//print_r($order_info);die();<--test
		$statusID = $order['status'];
		$order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE, $order['company_id']);
		$status_name = $order_statuses[$statusID]['description'];
		$customerId = $order['user_id'];
		$totalShipping = $order_info['shipping_cost'];
		$total = $order['total'];
		
		$discount = $order_info['subtotal_discount'];
		if($discount > 0 && $order_info['promotion_ids'] > 0){
			$discount_id = $order_info['promotion_ids'];
			$discount_data = db_get_field("SELECT conditions FROM ?:promotions WHERE promotion_id =?i", $discount_id);
			$discount_data = unserialize($discount_data);
			if(isset($discount_data['conditions'][1]['value']) && !empty($discount_data['conditions'][1]['value'])){
				$discount_code = $discount_data['conditions'][1]['value'];
			} else {
				$discount_code = '';
			}
		} else {
			$discount_code = '';
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
		if(empty($lastname)){
			$lastname = $firstname;
		} else {
			$lastname = $lastname;
		}
		$email = $order['email'];
		if($order_info['s_phone'] !=''){
			$phone = $order_info['s_phone'];
		} else {
			$phone = $order_info['phone'];			
		}
		if($order_info['s_city'] !=''){
			$city = $order_info['s_city'];
		} else {
			$city = $order_info['b_city'];			
		}
		if($order_info['s_city'] !=''){
			$county = $order_info['s_county'];
		} else {
			$county = $order_info['b_county'];			
		}
		if($order_info['s_address'] !=''){
			$address = $order_info['s_address'];
		} else {
			$address = $order_info['b_address'];			
		}
		$order_status = fn_get_status_data($order_info['status'],'O');
		$order_status = $order_status['description'];
		$totalTax = $order_info['tax_subtotal'];
		
		$i = 0;
		$products_data = array();		
		//$products_data = $products_data['orders']['order'];
		foreach($order_info['products'] as $p){
			$product = fn_get_product_data($p['product_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));

			//categories names
			if(isset($product['main_category'])) {
				$category_names = array();
				$category_path = db_get_field('SELECT id_path FROM ?:categories WHERE category_id = ?i', $product['main_category']);
				if(empty($category_path)) {
					$category_names[] = fn_get_category_name($value['main_category'], CART_LANGUAGE);
				} else {
					$id_path = explode('/', $category_path);
					foreach($id_path as $v) {
						$category_names[] = fn_get_category_name($v, CART_LANGUAGE);
					}
				}
				$category_names = implode('|', $category_names);
			}
			//brand
			if(isset($settings_tm['match_brand']) && isset($product_data['product_features'][$settings_tm['match_brand']])){
				$brand = $product_data['product_features'][$settings_tm['match_brand']]['variants'][$product_data['product_features'][$settings_tm['match_brand']]['variant_id']]['variant'];
			} else {
				$brand = 'N/A';
			}
			$products_data[$i]['product_id'] = $p['product_id'];
			$products_data[$i]['sku'] = $p['product_code'];
			$products_data[$i]['name'] = $p['product'];
			$products_data[$i]['url'] = $p['product_url'];
			$products_data[$i]['main_image'] = $product['main_pair']['detailed']['image_path'];
			$products_data[$i]['category'] = $category_names;
			$products_data[$i]['brand'] = $brand;
			$products_data[$i]['quantity'] = $p['amount'];
			$products_data[$i]['variation_id'] = $p['product_code'];
			$products_data[$i]['variation_sku'] = $p['product_code'];
			$products_data[$i]['price'] = $p['subtotal'];
			$products_data[$i]['sale_price'] = round($p['price'],2);
			$i++;		
		}
		
		
		$ordersarr['orders']['order'][$k]['order_no'] = $orderId;
		$ordersarr['orders']['order'][$k]['order_status'] = $order_status;
		$ordersarr['orders']['order'][$k]['refund_value'] = 0;
		$ordersarr['orders']['order'][$k]['created_at'] = $date_created;
		$ordersarr['orders']['order'][$k]['email_address'] = $email;
		$ordersarr['orders']['order'][$k]['phone'] = $phone;
		$ordersarr['orders']['order'][$k]['firstname'] = $firstname;
		$ordersarr['orders']['order'][$k]['lastname'] = $lastname;
		$ordersarr['orders']['order'][$k]['city'] = $city;
		$ordersarr['orders']['order'][$k]['county'] = $county;
		$ordersarr['orders']['order'][$k]['address'] = $address;
		$ordersarr['orders']['order'][$k]['discount_code'] = $discount_code;
		$ordersarr['orders']['order'][$k]['discount_value'] = $discount;
		$ordersarr['orders']['order'][$k]['shipping'] = $totalShipping;
		$ordersarr['orders']['order'][$k]['tax'] = $totalTax;
		$ordersarr['orders']['order'][$k]['total_value'] = $total;
		$ordersarr['orders']['order'][$k]['products'] = $products_data;
	
	}
	$json = json_encode($ordersarr,true);
	fwrite($jsonfile, $json);
	fclose($jsonfile);
	
	//get json file
	header('Access-Control-Allow-Origin: *');
	header('Content-type: application/json');
	readfile($filename);
	exit();
} 

//PRODUCTS FEED
if ($mode == 'products_feed') {
	$settings_tm = Registry::get('addons.themarketer');
	if($settings_tm['tm_rest_key'] !=$_REQUEST['key']){
		die('wrong');
		exit();
	}
	
	function removeSpecial($string){
		$string = str_replace(array('[\', \']'), '', $string);
		$string = preg_replace('/\[.*\]/U', '', $string);
		$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
		$string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
		return strtolower(trim($string, '-'));
	}	
	//get all products ids
	$allproducts = db_get_array('SELECT product_id from ?:products WHERE status = "A"');
	
	//start xml schema
	$xml_schema = '<?xml version="1.0" encoding="UTF-8"?>
		<products>'; 
	foreach($allproducts as $k=>$product_id){
		//if($k < 10000){ // <-- test start
		//create xml data
			$pid = $product_id['product_id'];
			$product_data = fn_get_product_data($pid,$auth);
			$product_url = fn_url('products.view?product_id='.$pid);
			
			if($product_data['product_code'] !=''){
				//main image
				$product_main_image = $product_data['main_pair']['detailed']['image_path'];
				//categories names
				if(isset($product_data['main_category'])) {
					$category_names = array();
					$category_path = db_get_field('SELECT id_path FROM ?:categories WHERE category_id = ?i', $product_data['main_category']);
					if(empty($category_path)) {
						$category_names[] = fn_get_category_name($value['main_category'], CART_LANGUAGE);
					} else {
						$id_path = explode('/', $category_path);
						foreach($id_path as $v) {
							$category_names[] = fn_get_category_name($v, CART_LANGUAGE);
						}
					}
					$category_names = implode('|', $category_names);
				}
				
				//brand
				if(isset($settings_tm['match_brand']) && isset($product_data['product_features'][$settings_tm['match_brand']])){
					$brand = $product_data['product_features'][$settings_tm['match_brand']]['variants'][$product_data['product_features'][$settings_tm['match_brand']]['variant_id']]['variant'];
				} else {
					$brand = 'N/A';
				}
				//get stock 
				if($product_data['amount'] > 0){
					$availability = 1;
					$availability_supplier = 2;
				} else {
					$availability = 0;
					$availability_supplier = 0;			
				}
				$images = $product_data['image_pairs'];
				$media = '';
				foreach($images as $img){					
					$media .= '<image>'.$img['detailed']['image_path'].'</image>';
				}
				//compinations
				$compination = '';
				if(count($product_data['product_features']) > 0){
					
					$vars = array();
					foreach($product_data['product_features'] as $k=>$c){
						
						if($c['feature_id'] == $settings_tm['match_size'] && $product_data['product_features'][$settings_tm['match_size']]['variants'][$product_data['product_features'][$settings_tm['match_size']]['variant_id']]['variant'] !=''){ $size = $product_data['product_features'][$settings_tm['match_size']]['variants'][$product_data['product_features'][$settings_tm['match_size']]['variant_id']]['variant'];} else {}
						if($c['feature_id'] == $settings_tm['match_color'] && $product_data['product_features'][$settings_tm['match_color']]['variants'][$product_data['product_features'][$settings_tm['match_color']]['variant_id']]['variant'] !=''){ $color = $product_data['product_features'][$settings_tm['match_color']]['variants'][$product_data['product_features'][$settings_tm['match_color']]['variant_id']]['variant'];} else {}
					}
					if($product_data['amount'] > 0){ $avail = 1;} else { $avail = 0;}
					if($product_data['amount'] > 0){ $product_data['amount'] = $product_data['amount'];} else { $product_data['amount'] = 0;}
					if(!empty($size) || !empty($color)){
						if($product_data['list_price'] > 0){$product_data['list_price'] = $product_data['list_price'];} else {$product_data['list_price'] = $product_data['price'];}
						$compination .= '<variation>';
						$compination .= '<id>'.$product_data['product_code'].'</id>';
						$compination .= '<sku>'.$product_data['product_code'].'</sku>';
						$compination .= '<acquisition_price>0</acquisition_price>';
						$compination .= '<price>'.round($product_data['list_price'],2).'</price>';
						$compination .= '<sale_price>'.round($product_data['price'],2).'</sale_price>';
						if(!empty($size)){
							$compination .= '<size><![CDATA['.$size.']]></size>';
						} else {'';}
						if(!empty($color)){
							$compination .= '<color><![CDATA['.$color.']]></color>';
						} else {'';}
						$compination .= '<availability>'.$avail.'</availability>';
						$compination .= '<stock>'.$product_data['amount'].'</stock>';								
						$compination .= '</variation>';
					} else {}
				}
				if(!empty($compination)){$variations ="<variations>".$compination."</variations>";} else {$variations ="";}
				if(!empty($media)) { $media_tag = " <media_gallery>".$media."</media_gallery>";} else {$media_tag = "";}
				if($product_data['amount'] < 0){$product_data['amount'] = 0;} else {$product_data['amount'] = $product_data['amount'];}
				if($product_data['list_price'] > 0){$product_data['list_price'] = $product_data['list_price'];} else {$product_data['list_price'] = $product_data['price'];}
				if(isset($product_data['offer_date_from'])){ $offer_date_from = date('Y-m-d H:i',$product_data['offer_date_from']); $offer_date_to = date('Y-m-d H:i',$product_data['offer_date_to']);} else {$offer_date_from = date('Y-m-d H:i'); $offer_date_to = date('Y-m-d H:i',strtotime(' + 5 years'));}
				$xml_schema .= "
					<product>	
						<id>".$pid."</id>
						<sku><![CDATA[".$product_data['product_code']."]]></sku>
						<name><![CDATA[".$product_data['product']."]]></name>
						<description><![CDATA[".removeSpecial(strip_tags($product_data['full_description']))."]]></description>
						<url><![CDATA[".$product_url."]]></url>
						<main_image>".$product_main_image."</main_image>
						<category><![CDATA[".$category_names."]]></category>
						<brand><![CDATA[".$brand."]]></brand>
						<acquisition_price>0</acquisition_price>
						<price>".round($product_data['list_price'],2)."</price>
						<sale_price>".round($product_data['price'],2)."</sale_price>
						<sale_price_start_date>".$offer_date_from."</sale_price_start_date>
						<sale_price_end_date>".$offer_date_to."</sale_price_end_date>
						<availability>$availability</availability>
						<stock>".$product_data['amount']."</stock>		
						".$media_tag."
						".$variations."
						<created_at>".date('Y-m-d H:i',$product_data['timestamp'])."</created_at>
					</product>	
				";
			} else {}
		//} // <-- test end
	}
	//end xml schema
	$xml_schema .= '</products>';
	//create file
	$filename = Registry::get('config.dir.var') . 'files/themarketer/products_feed_'.$_REQUEST['key'].'.xml';
	$xmlfile = fopen($filename, "w");
	fwrite($xmlfile, $xml_schema);
	fclose($xmlfile);
	
	//get xml file
	header("Content-Type: text/xml;charset=utf-8");
	readfile($filename);
	exit();
	
}

//CATEGORIES FEED
if ($mode == 'categories_feed') {
	$settings_tm = Registry::get('addons.themarketer');
	if($settings_tm['tm_rest_key'] !=$_REQUEST['key']){
		die('wrong');
		exit();
	}
	$langid = CART_LANGUAGE;
	$all_categories = db_get_array('SELECT category_id,category from ?:category_descriptions WHERE lang_code = "'.$langid.'"');
	
	//start xml schema
	$xml_schema = '<?xml version="1.0" encoding="UTF-8"?>
					<categories>'; 
	foreach($all_categories as $cat){
		$category_names = array();
		$category_path = db_get_field('SELECT id_path FROM ?:categories WHERE category_id = ?i', $cat['category_id']);
		if(empty($category_path)) {
			$category_names[] = '';//fn_get_category_name($cat['category_id'], CART_LANGUAGE);
		} else {
			$id_path = explode('/', $category_path);
			foreach($id_path as $v) {
				$category_names[] = fn_get_category_name($v, CART_LANGUAGE);
			}
		}	
		$category_names = implode('|', $category_names);
		$category_url = fn_url('categories.view?category_id='.$cat['category_id']);//category url
		//category schema
		 $xml_schema .=  "<category>
			<name><![CDATA[".$cat['category']."]]></name>
			<url><![CDATA[".$category_url."]]></url>
			<id>".$cat['category_id']."</id>
			<hierarchy><![CDATA[".$category_names."]]></hierarchy>
		  </category>";		
			
	}		
	
	//end xml schema
	$xml_schema .= '</categories>';
	//create file
	$filename = Registry::get('config.dir.var') . 'files/themarketer/categories_feed.xml';
	$xmlfile = fopen($filename, "w");
	fwrite($xmlfile, $xml_schema);
	fclose($xmlfile);
	
	//get xml file
	header("Content-Type: text/xml;charset=utf-8");
	readfile($filename);
	exit();
}

//CATEGORIES BRANDS
if ($mode == 'brands_feed') {
	$settings_tm = Registry::get('addons.themarketer');
	if($settings_tm['tm_rest_key'] !=$_REQUEST['key']){
		die('wrong');
		exit();
	}
	
	$brands_code = $settings_tm['match_brand'];
	$brands = db_get_array('SELECT ?:product_feature_variants.variant_id as vid, ?:product_feature_variant_descriptions.variant as name FROM ?:product_feature_variants,?:product_feature_variant_descriptions WHERE ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id AND ?:product_feature_variants.feature_id='.$brands_code.' AND ?:product_feature_variant_descriptions.lang_code=?s', CART_LANGUAGE);
	
	//start xml schema
	$xml_schema = '<?xml version="1.0" encoding="UTF-8"?>
					<brands>'; 
					
	foreach($brands	as $brand){		
		$brand_data = fn_get_product_feature_variant($brand['vid']);
		//brand logo-image url
		if(isset($brand_data['image_pair']['icon']['image_path']) && !empty($brand_data['image_pair']['icon']['image_path'])){
			$logo = $brand_data['image_pair']['icon']['image_path'];
		} else {$logo ='';}
		//brand url
		if(empty($brand_data['url'])){
			$brand_url = db_get_field('SELECT name from ?:seo_names WHERE type="e" AND object_id = ?i ',$brand['vid']);
			$brand_url = 'https://'.Registry::get('config.https_host').Registry::get('config.https_path').'/'.$brand_url;
		} else {
			$brand_url = $brand_data['url'];
		}
		$xml_schema .= "<brand>
			<name><![CDATA[".$brand['name']."]]></name>
			<url><![CDATA[".$brand_url."]]></url>
			<id>".$brand['vid']."</id>
			<image_url>".$logo."</image_url>
		</brand>";
	}	
	//end xml schema
	$xml_schema .= '</brands>';
	//create file
	$filename = Registry::get('config.dir.var') . 'files/themarketer/brands_feed.xml';
	$xmlfile = fopen($filename, "w");
	fwrite($xmlfile, $xml_schema);
	fclose($xmlfile);
	
	//get xml file
	header("Content-Type: text/xml;charset=utf-8");
	readfile($filename);
	exit();	
}

//product data
if ($mode == 'get_product') { 
	if($_REQUEST['type'] == 'addtocart'){
		$product = fn_get_product_data($_REQUEST['product_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));
		echo $product['product_code'];
	} else {}
	if($_REQUEST['type'] == 'removecart'){
		$product = fn_get_product_data($_REQUEST['product_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));
		echo $product['product_code'];
	} else {}
	if($_REQUEST['type'] == 'addtowish' || $_REQUEST['type'] == 'removewish'){
		$product = fn_get_product_data($_REQUEST['product_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));
		echo $product['product_code'];
	} else {}
	
	if ($_REQUEST['type'] == 'getcart') {
		$cartdata = $_SESSION['cart']['products'][$_POST['cart_id']];
		echo $cartdata['product_id'].'@'.$cartdata['product_code'].'@'.$cartdata['amount'];
	} else {}
}


if ($mode == 'add_wishlist') {
    // Add product to the wishlist
    if ($_POST['product_id']) {
		$product = fn_get_product_data($_POST['product_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));
		echo $product['product_code'];
	}
}

//subscribe newsletter
if ($mode == 'subscribenl') {
	$email = db_get_field('SELECT subscriber_id FROM ?:subscribers WHERE email = ?s ',$_REQUEST['email']);
	//if($_REQUEST['email'] !=''){
		$settings_tm = Registry::get('addons.themarketer');
		$apiURL = 'https://t.themarketer.com/api/v1/add_subscriber';
		$apiKey = $settings_tm['tm_rest_key'];
		$customerId = $settings_tm['tm_customer_id'];		
		$registerData = [
			"k" => $apiKey,
			"u" => $customerId,
			"email" => $_REQUEST['email'], //required
			"phone" => "", //optional
			"name" => "" //optional
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
        print_r($response);	
	//} else {}
}
//Syncing product reviews
if ($mode == 'reviews') {	
	$settings_tm = Registry::get('addons.themarketer');
    $apiURL = 'https://t.themarketer.com/api/v1/product_reviews?k=' . $apiKey . '&u=' . $customerId . '&t=' . $start_date;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, '30');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
	
	//get xml file
	header("Content-Type: text/xml;charset=utf-8");
	readfile($response);
	exit();	
}
//Create a coupon
if ($mode == 'create_coupon') {	
	$settings_tm = Registry::get('addons.themarketer');
	$params = $_REQUEST;
	if($settings_tm['tm_rest_key'] ==$params['key'] && isset($_REQUEST['type']) && isset($_REQUEST['value']) && isset($_REQUEST['expiration_date'])){

		$nowtime = time();
		$tomorow = strtotime($params['expiration_date']);
		$couponcode = strtoupper(uniqid());
		$discount = (int)$params['value'];	
		$type = $params['type'];
		$lang_id = CART_LANGUAGE;
		if($type == 0){
			$type_id = 'to_fixed';
		}
		else if($type == 1){
			$type_id = 'to_percentage';
		} else {
			$type_id = 'free_shipping';
		}
		if($type != 2){
			$conditions['set'] = 'all';
			$conditions['set_value'] = 1;
			$conditions['conditions'][1] = array( 'operator' => 'gte', 'condition' => 'subtotal', 'value' => 0.0 );
			$conditions['conditions'][2] = array('condition' => 'once_per_customer', 'operator' => 'eq', 'value' => 'Y',); 
			$conditions['conditions'][4] = array ('operator' => 'eq',  'condition' => 'coupon_code',  'value' => $couponcode,);
				
			$conditions = serialize($conditions); 
			$bonus[1] = 
				  array(
					'bonus' => 'order_discount',
					'discount_bonus' => $type_id,
					'discount_value' => $discount,
				);
			$bonus = serialize($bonus); 
			db_query("INSERT INTO `?:promotions` (`company_id`, `conditions`, `bonuses`, `to_date`, `from_date`, `priority`, `stop`, `stop_other_rules`, `zone`, `conditions_hash`, `status`, `number_of_usages`, `users_conditions_hash`) VALUES
			( 1, '$conditions', '$bonus',$tomorow ,$nowtime , 0, 'N', 'Y', 'cart', 'coupon_code=$couponcode;once_per_customer=Y', 'A', 0, '')");
			$proid = db_get_field('SELECT promotion_id FROM ?:promotions ORDER BY promotion_id DESC LIMIT 1');
			db_query("INSERT INTO `?:promotion_descriptions` (`promotion_id`, `name`, `short_description`, `detailed_description`, `lang_code`) VALUES ($proid, 'Themarketer Discount Coupon #$couponcode', 'Themarketer Discount Coupon', 'Themarketer Discount Coupon', '$lang_id')");
			$discountCode = json_encode($couponcode);
		} else {//get free shipping discount
			$conditions['set'] = 'all';
			$conditions['set_value'] = 1;
			$conditions['conditions'][2] = array('condition' => 'once_per_customer', 'operator' => 'eq', 'value' => 'Y',); 
			$conditions['conditions'][4] = array ('operator' => 'eq',  'condition' => 'coupon_code',  'value' => $couponcode,);

			$conditions = serialize($conditions); 
			$bonus[2] = 
				  array(
					'bonus' => 'free_shipping',
					'value' => 1,
				);
			$bonus = serialize($bonus); 
			db_query("INSERT INTO `?:promotions` (`company_id`, `conditions`, `bonuses`, `to_date`, `from_date`, `priority`, `stop`, `stop_other_rules`, `zone`, `conditions_hash`, `status`, `number_of_usages`, `users_conditions_hash`) VALUES
			( 1, '$conditions', '$bonus',$tomorow ,$nowtime , 0, 'N', 'Y', 'cart', 'coupon_code=$couponcode;once_per_customer=Y', 'A', 0, '')");
			$proid = db_get_field('SELECT promotion_id FROM ?:promotions ORDER BY promotion_id DESC LIMIT 1');
			db_query("INSERT INTO `?:promotion_descriptions` (`promotion_id`, `name`, `short_description`, `detailed_description`, `lang_code`) VALUES ($proid, 'Themarketer Free Shipping Coupon #$couponcode', 'Themarketer Free Shipping Coupon', 'Themarketer Free Shipping Coupon', '$lang_id')");
			$discountCode = json_encode($couponcode);			
		}
	} else {
		$discountCode = json_encode(['status' => 'Incorrect REST API Key']);
	}	
	//create json file
	$filename = Registry::get('config.dir.var') . 'files/themarketer/new_coupon.json';
	$jsonfile = fopen($filename, "w");
	fwrite($jsonfile, $discountCode);
	fclose($jsonfile);
	 
	//get json file
	header('Access-Control-Allow-Origin: *');
	header('Content-type: application/json');
	readfile($filename);
	exit();	
}