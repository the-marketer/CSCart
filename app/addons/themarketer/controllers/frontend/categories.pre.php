<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$view = Tygh::$app['view'];

if ($mode == 'view') {
	$cat_id = $_REQUEST['category_id'];
		$category_path = db_get_field('SELECT id_path FROM ?:categories WHERE category_id = ?i', $cat_id);
		if(empty($category_path)) {
			$category_names[] = '';//fn_get_category_name($cat['category_id'], CART_LANGUAGE);
		} else {
			$id_path = explode('/', $category_path);
			foreach($id_path as $v) {
				$category_names[] = fn_get_category_name($v, CART_LANGUAGE);
			}
		}	
		$category_names = implode('|', $category_names);	
		$categories = '<script>var categories_names = "'.$category_names.'"</script>';
	Registry::get('view')->assign('category_names', $category_names);	
	//fn_set_notification('W', __('notice'), var_export($category_names,true));
	
}