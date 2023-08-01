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

if (!defined('MKTR_ROOT')) {
    define('MKTR_ROOT', DIR_ROOT . (substr(DIR_ROOT, -1) === '/' ? '' : '/'));
}
if (!defined('MKTR_APP')) {
    define('MKTR_APP', __DIR__ . (substr(__DIR__, -1) === '/' ? '' : '/'));
}

if (!function_exists('function_name')) {
    function dd()
    {
        echo '<pre>';
        foreach (func_get_args() as $variable) {
            var_dump($variable);
        } echo '</pre>';
        exit;
    }
}

// , 'init_layout');
// ,'init_templater_post');
// ,'smarty_function_script_after_formation');

class Mktr
{
    private static $i = null;
    private static $included = [];
    public static $auth = null;

    public function __construct()
    {
        self::$i = $this;
        spl_autoload_register([$this, 'load'], true, true);
    }

    public static function i()
    {
        if (self::$i === null) {
            self::$i = new static();
        }

        return self::$i;
    }

    private function finLoad()
    {
        spl_autoload_unregister([self::i(), 'load']);
    }

    private function load($className, $ext = '.php')
    {
        if (strpos($className, 'Mktr\\') !== false) {
            if (!array_key_exists($className, self::$included)) {
                $file = MKTR_APP . str_replace(['Mktr\\', '\\'], ['App/', '/'], $className) . $ext;
                if (file_exists($file)) {
                    self::$included[$className] = true;
                    require_once $file;
                } else {
                    self::$included[$className] = false;
                }
            }
        }
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } else {
            if (DEVELOPMENT) {
                throw new \Exception("Method {$name} does not exist.");
            }

            return null;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::i(), $name)) {
            return call_user_func_array([self::$i, $name], $arguments);
        } else {
            if (DEVELOPMENT) {
                throw new \Exception("Static method {$name} does not exist.");
            }

            return null;
        }
    }
}

function fn_mktr_get_route(&$req, &$result, &$area, &$is_allowed_url)
{
    if (array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/mktr\/api\/([^\/?]+)/', $_SERVER['REQUEST_URI'], $matches)) {
        $req['dispatch'] = 'mktr.api.' . $matches[1];
        $is_allowed_url = 1;
    }
}

function fn_mktr_install()
{
    Mktr::i();
    Mktr\Helper\Admin::install();
}

function fn_mktr_uninstall()
{
    Mktr::i();
    Mktr\Helper\Admin::uninstall();
}

function fn_get_mktr_form()
{
    Mktr::i();

    return Mktr\Helper\Admin::form();
}

function fn_mktr_change_order_status(&$nStatus = null, &$fStatus = null, &$order = null)
{
    Mktr::i();
    if ($nStatus !== null && Mktr\Model\Config::rest()) {
        $send = [
            'order_number' => $order['order_id'],
            'order_status' => \Mktr\Model\Orders::orderState($nStatus),
        ];

        \Mktr\Helper\Api::send('update_order_status', $send, false);
    }
}

function fn_mktr_add_features($NewCart)
{
    $list = [];
    foreach ($_REQUEST as $k => $v) {
        if (strpos($k, 'feature_') !== false) {
            $list[str_replace('feature_', '', $k)] = $v;
        }
    }

    return $list;
}

function fn_mktr_add_to_cart($cart, $product_id, $_id)
{
    Mktr::i();
    $NewCart = $cart['products'][$_id];
    $NewCart['product_features'] = fn_mktr_add_features($NewCart);
    $data = \Mktr\Model\Product::getProductFromCartData($NewCart);
    Mktr\Helper\Session::addToCart($data['pId'], $data['pAttr'], $cart['products'][$_id]['amount']);
    Mktr\Helper\Session::save();
}

function fn_mktr_delete_cart_product($cart, $_id, $full_erase)
{
    Mktr::i();
    $NewCart = $cart['products'][$_id];
    $NewCart['product_features'] = fn_mktr_add_features($NewCart);
    $data = \Mktr\Model\Product::getProductFromCartData($NewCart);
    Mktr\Helper\Session::removeFromCart($data['pId'], $data['pAttr'], $cart['products'][$_id]['amount']);
    Mktr\Helper\Session::save();
}
function fn_mktr_pre_add_to_wishlist($product_data, $wishlist, $auth)
{
    Mktr::i();
    $product_data = end($product_data);
    $data = \Mktr\Model\Product::getProductFromCartData($product_data);
    Mktr\Helper\Session::addToWishlist($data['pId'], $data['pAttr']);
    Mktr\Helper\Session::save();
}

function fn_mktr_delete_wishlist_product($wishlist, $wishlist_id)
{
    Mktr::i();
    $data = \Mktr\Model\Product::getProductFromCartData($wishlist['products'][$wishlist_id]);
    Mktr\Helper\Session::removeFromWishlist($data['pId'], $data['pAttr']);
    Mktr\Helper\Session::save();
}

function fn_mktr_newsletters_update_subscriptions_post($subscriber_id, $user_list_ids, $subscriber, $params)
{
    if (array_key_exists('dispatch', $_REQUEST)) {
        Mktr::i();
        if ($_REQUEST['dispatch'] == 'newsletters.add_subscriber') {
            if (array_key_exists('subscribe_email', $_REQUEST) && !empty($_REQUEST['subscribe_email'])) {
                \Mktr\Helper\Session::setEmail($_REQUEST['subscribe_email']);
                \Mktr\Helper\Session::save();
            }
        } elseif ($_REQUEST['dispatch'] == 'profiles.update') {
            if (array_key_exists('user_data', $_REQUEST) && !empty($_REQUEST['user_data']['email'])) {
                \Mktr\Helper\Session::setEmail($_REQUEST['user_data']['email']);
                \Mktr\Helper\Session::save();
            } elseif (array_key_exists('email', $subscriber) && !empty($subscriber['email'])) {
                \Mktr\Helper\Session::setEmail($subscriber['email']);
                \Mktr\Helper\Session::save();
            }
        }
    }
}

function fn_mktr_login_user_post($user_id, $cu_id, $udata, $auth, $condition, $result)
{
    if (array_key_exists('dispatch', $_REQUEST) && $_REQUEST['dispatch'] == 'auth.login') {
        Mktr::i();
        if (array_key_exists('email', $auth) && !empty($auth['email'])) {
            \Mktr\Helper\Session::setEmail($auth['email']);
        } elseif (array_key_exists('email', $udata) && !empty($udata['email'])) {
            \Mktr\Helper\Session::setEmail($udata['email']);
        }
        if (array_key_exists('phone', $auth) && !empty($auth['phone'])) {
            \Mktr\Helper\Session::setPhone($auth['phone']);
        } elseif (array_key_exists('phone', $udata) && !empty($udata['phone'])) {
            \Mktr\Helper\Session::setPhone($udata['phone']);
        }

        \Mktr\Helper\Session::save();
    }
}

if (AREA !== 'A') {
    // dd($_GET, $_REQUEST);
}
