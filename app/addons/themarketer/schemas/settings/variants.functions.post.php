<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_themarketer_match_brand() {
	$lang_id = CART_LANGUAGE;
	$features = db_get_array('SELECT `feature_id`,`description` FROM `?:product_features_descriptions` WHERE lang_code ="'.$lang_id.'"');

	if(!empty($features)) {
		foreach($features as $k => $v) {
			$features_list[$v['feature_id']] = $v['description'].' (feature)';
		}
	}
	
	return $features_list;

}


function fn_settings_variants_addons_themarketer_match_size() {
	$lang_id = CART_LANGUAGE;
	$features = db_get_array('SELECT `feature_id`,`description` FROM `?:product_features_descriptions` WHERE lang_code ="'.$lang_id.'"');

	if(!empty($features)) {
		foreach($features as $k => $v) {
			$features_list[$v['feature_id']] = $v['description'].' (feature)';
		}
	}
	
	return $features_list;
}



function fn_settings_variants_addons_themarketer_match_color() {
	$lang_id = CART_LANGUAGE;
	$features = db_get_array('SELECT `feature_id`,`description` FROM `?:product_features_descriptions` WHERE lang_code ="'.$lang_id.'"');

	if(!empty($features)) {
		foreach($features as $k => $v) {
			$features_list[$v['feature_id']] = $v['description'].' (feature)';
		}
	}
	
	return $features_list;
}