<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/
if (!defined('BOOTSTRAP')) {
    exit('Access denied');
}

fn_register_hooks(
    'get_route',
    'change_order_status',
    'delete_cart_product',
    'add_to_cart',
    // 'pre_add_to_cart',
    'pre_add_to_wishlist',
    'delete_wishlist_product',
    'newsletters_update_subscriptions_post',
    'login_user_post',
    'api_handle_request',
    'api_check_access'
);
