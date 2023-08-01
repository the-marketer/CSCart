<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Model;

class Config
{
    const CONFIG_DATA = [
        'status' => ['default' => false, 'type' => 'bool'],
        'tracking_key' => ['default' => '', 'type' => 'string'],
        'rest_key' => ['default' => '', 'type' => 'string'],
        'customer_id' => ['default' => '', 'type' => 'string'],
        'cron_feed' => ['default' => true, 'type' => 'bool'],
        'update_feed' => ['default' => 4, 'type' => 'int'],
        'cron_review' => ['default' => false, 'type' => 'bool'],
        'update_review' => ['default' => 4, 'type' => 'int'],
        'opt_in' => ['default' => 0, 'type' => 'int'],
        'push_status' => ['default' => false, 'type' => 'bool'],
        'default_stock' => ['default' => 0, 'type' => 'int'],
        'allow_export' => ['default' => true, 'type' => 'bool'],
        'selectors' => ['default' => '', 'type' => 'string'],
        'brand' => ['default' => ['Brand'], 'type' => 'array'],
        'color' => ['default' => ['Color'], 'type' => 'array'],
        'size' => ['default' => ['Size'], 'type' => 'array'],
        'google_status' => ['default' => false, 'type' => 'bool'],
        'google_tagCode' => ['default' => '', 'type' => 'string'],
    ];

    protected $attributes = [
        'status' => null,
        'tracking_key' => null,
        'rest_key' => null,
        'customer_id' => null,
        'cron_feed' => null,
        'update_feed' => null,
        'cron_review' => null,
        'update_review' => null,
        'opt_in' => null,
        'push_status' => null,
        'default_stock' => null,
        'allow_export' => null,
        'selectors' => null,
        'brand' => null,
        'color' => null,
        'size' => null,
        'google_status' => null,
        'google_tagCode' => null,
    ];

    const FIREBASE_CONFIG = 'const firebaseConfig = {
    apiKey: "AIzaSyA3c9lHIzPIvUciUjp1U2sxoTuaahnXuHw",
    projectId: "themarketer-e5579",
    messagingSenderId: "125832801949",
    appId: "1:125832801949:web:0b14cfa2fd7ace8064ae74"
};

firebase.initializeApp(firebaseConfig);';

    const FIREBASE_MESSAGING_SW = 'importScripts("https://www.gstatic.com/firebasejs/9.4.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/9.4.0/firebase-messaging-compat.js");
importScripts("./firebase-config.js");
importScripts("https://t.themarketer.com/firebase.js");';

    protected $load = [];

    public $isDirty = false;
    public static $dateFormat = 'Y-m-d';
    public static $dateFormatParam = 'Y-m-d';

    private $hide = [];
    private static $i = null;
    private static $shop = null;
    private static $cShop = null;
    private static $nws = null;
    private static $db = null;
    private static $product_features = null;

    private static $checkData = [
        'showJs' => null,
        'showGoogle' => null,
        'rest' => null,
    ];

    private static $configData = [
        'tracker_status' => null,
        'tracker_settings' => null,
        'google_status' => null,
        'google_settings' => null,
    ];

    public static function i($new = false)
    {
        if (self::$i === null || $new === true) {
            if (self::$i !== null) {
                self::$shop = null;
                self::$cShop = null;
                self::$nws = null;
                self::$db = null;
                self::$product_features = null;

                self::$checkData = [
                    'showJs' => null,
                    'showGoogle' => null,
                    'rest' => null,
                ];

                self::$configData = [
                    'tracker_status' => null,
                    'tracker_settings' => null,
                    'google_status' => null,
                    'google_settings' => null,
                ];
            }

            self::$i = new static();
        }

        return self::$i;
    }

    public static function db()
    {
        if (self::$db === null) {
            self::$db = \Tygh\Tygh::$app['db'];
        }

        return self::$db;
    }

    public static function shop()
    {
        if (self::$shop === null) {
            self::$shop = isset($_REQUEST['storefront_id']) ? (int) $_REQUEST['storefront_id'] : 0;
            // self::$shop = isset($_REQUEST['storefront_id']) ? (int) $_REQUEST['storefront_id'] :  \Tygh\Registry::get('runtime.storefront_id');
        }

        return self::$shop;
    }

    public static function cShop()
    {
        if (self::$cShop === null) {
            $params = self::shop() !== 0 ? ['storefront_id' => self::shop()] : [];
            self::$cShop = \Tygh\Settings::instance($params);
        }

        return self::$cShop;
    }

    public static function setShop($ID = null)
    {
        if ($ID !== null) {
            self::$shop = (int) $ID;
            $params = self::$shop !== 0 ? ['storefront_id' => self::$shop] : [];
            self::$cShop = \Tygh\Settings::instance($params);
        }

        return self::$cShop;
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

    public function __get($name)
    {
        if ($this->attributes[$name] === null) {
            switch ($name) {
                case 'status':
                    $v = [$name => self::getConfig('tracker_status')];
                    break;
                case 'google_status':
                    $v = [$name => self::getConfig('google_status')];
                    break;
                case 'google_tagcode':
                    $v = self::getConfig('google_settings');
                    break;
                default:
                    $v = self::getConfig('tracker_settings');
            }

            $this->attributes[$name] = isset($v[$name]) ? $v[$name] : false;

            if (!in_array(self::CONFIG_DATA[$name]['type'], ['bool', 'boolean']) && $this->attributes[$name] === false) {
                $this->attributes[$name] = self::CONFIG_DATA[$name]['default'];
            } else {
                $this->attributes[$name] = $this->cast($name, $this->attributes[$name]);
            }

            $this->load[$name] = true;
        }

        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        $this->isDirty = true;
        $this->attributes[$name] = $value;
        $this->load[$name] = true;
    }

    private function getConfig($name, $section_name = 'mktr')
    {
        if (!array_key_exists($name, $this->attributes) || $this->attributes[$name] === null) {
            $this->attributes[$name] = self::cShop()->getValue($name, $section_name);
            if (in_array($name, ['google_settings', 'tracker_settings', 'email_status'])) {
                $this->attributes[$name] = self::unserialized($this->attributes[$name]);
            }
        }

        return $this->attributes[$name];
    }

    private function setConfig($name, $value, $section_name = 'mktr')
    {
        if (in_array($name, ['google_settings', 'tracker_settings', 'email_status'])) {
            self::cShop()->updateValue($name, self::serialized($value), $section_name, true);
        } else {
            self::cShop()->updateValue($name, $value, $section_name, true);
        }

        $this->attributes[$name] = $value;

        return $this;
    }

    private function save()
    {
        if ($this->isDirty) {
            $this->isDirty = false;
            $update = [];
            foreach ($this->load as $key => $value) {
                $value1 = $this->attributes[$key];
                switch ($key) {
                    case 'status':
                        $update['tracker_status'] = true;
                        $this->attributes['tracker_status'] = $value1;
                        break;
                    case 'google_status':
                        $update['google_status'] = true;
                        $this->attributes['google_status'] = $value1;
                        break;
                    case 'google_tagcode':
                        $update['google_settings'] = true;
                        $this->attributes['google_settings'][$key] = $value1;
                        break;
                    default:
                        $update['tracker_settings'] = true;
                        $this->attributes['tracker_settings'][$key] = $value1;
                }
            }

            foreach ($update as $key => $value) {
                $this->setConfig($key, self::getConfig($key));
            }
        }
    }

    public static function nws()
    {
        if (self::$nws === null) {
            self::$nws = [
                'CONFIRMATION' => ['em_double_opt_in', 'email_marketing'],
                'NOTIFICATION' => ['em_welcome_letter', 'email_marketing'],
            ];
        }

        return self::$nws;
    }

    private function updateOptIn()
    {
        $data = self::nws();

        if ($this->opt_in == 0) {
            self::setConfig($data['CONFIRMATION'][0], 'Y', $data['CONFIRMATION'][1]);
            self::setConfig($data['NOTIFICATION'][0], 'Y', $data['NOTIFICATION'][1]);
        } else {
            self::setConfig($data['CONFIRMATION'][0], 'N', $data['CONFIRMATION'][1]);
            self::setConfig($data['NOTIFICATION'][0], 'N', $data['NOTIFICATION'][1]);
        }
    }

    public static function updatePushStatus()
    {
        if (method_exists(self::cShop(), 'getAllStorefrontValues')) {
            $storefronts = self::cShop()->getAllStorefrontValues('tracker_settings', 'mktr');
        } else {
            $storefronts = [self::cShop()->getValue('tracker_settings', 'mktr')];
        }
        if (self::i()->push_status === true) {
            $is = true;
        } else {
            $is = false;
            foreach ($storefronts as $key => $data) {
                $d = self::unserialized($data);
                if ($key !== self::shop() && (bool) $d['push_status'] === true) {
                    $is = true;
                    break;
                }
            }
        }

        if ($is === true) {
            \Mktr\Helper\FileSystem::write('firebase-config.js', self::FIREBASE_CONFIG, true);
            \Mktr\Helper\FileSystem::write('firebase-messaging-sw.js', self::FIREBASE_MESSAGING_SW, true);
        } else {
            if (file_exists(MKTR_ROOT . '/firebase-config.js')) {
                unlink(MKTR_ROOT . '/firebase-config.js');
            }
            if (file_exists(MKTR_ROOT . '/firebase-messaging-sw.js')) {
                unlink(MKTR_ROOT . '/firebase-messaging-sw.js');
            }
        }
    }

    public static function delete($name = null, $section_name = 'mktr')
    {
        if ($name === null) {
            foreach (['tracker_status', 'tracker_settings', 'google_status', 'google_settings'] as $v) {
                $oid = self::cShop()->getId($v, $section_name);
                self::db()->query('DELETE FROM ?:settings_vendor_values WHERE object_id = ?i AND storefront_id = ?i', $oid, self::shop());
            }
        } else {
            $oid = self::cShop()->getId($name, $section_name);
            self::db()->query('DELETE FROM ?:settings_vendor_values WHERE object_id = ?i AND storefront_id = ?i', $oid, self::shop());
        }
    }

    public static function serialized($value)
    {
        return serialize($value);
        // return base64_encode(is_array($value) ? serialize($value) : serialize(array($value)));
    }

    public static function unserialized($value)
    {
        return unserialize($value);
        // return unserialize(base64_decode($value));
    }
/*
    public function asString($name)
    {
        $value = $this->{$name};

        if (self::CONFIG_DATA[$name]['type'] === 'array' && $value !== null) {
            $value = implode('|', (array) $value);
        }

        return $value;
    }
*/

    private function getSeo()
    {
        $name = 'seo';
        if (!array_key_exists($name, $this->attributes) || $this->attributes[$name] === null) {
            $this->attributes[$name] = self::db()->getField('SELECT status FROM ?:addons WHERE addon = ?s', 'seo') == 'A';
        }

        return $this->attributes[$name];
    }

    public static function rest($new = false)
    {
        if ($new === true || self::$checkData['rest'] === null) {
            $i = self::i();
            self::$checkData['rest'] = $i->status && $i->tracking_key !== '' && $i->rest_key !== '' && $i->customer_id !== '';
        }

        return self::$checkData['rest'];
    }

    public static function showGoogle($new = false)
    {
        if ($new === true || self::$checkData['showGoogle'] === null) {
            $i = self::i();
            self::$checkData['showGoogle'] = $i->google_status && $i->google_tagCode;
        }

        return self::$checkData['showGoogle'];
    }

    public static function showJs($new = false)
    {
        if ($new === true || self::$checkData['showJs'] === null) {
            $i = self::i();
            self::$checkData['showJs'] = $i->status && $i->tracking_key !== '';
        }

        return self::$checkData['showJs'];
    }

    public static function searchIn($array, $field, $idField = null)
    {
        $out = [];

        if (is_array($array)) {
            foreach ($array as $value) {
                if ($idField == null) {
                    $out[] = $value[$field];
                } else {
                    $out[$value[$idField]] = $value[$field];
                }
            }

            return $out;
        } else {
            return false;
        }
    }

    public static function AddDefault()
    {
        $i = self::i();

        foreach (self::CONFIG_DATA as $key => $v) {
            if (in_array($key, ['brand', 'color', 'size'])) {
                $i->{$key} = self::product_features($v['default'][0]);
            } else {
                $i->{$key} = $v['default'];
            }
        }

        $i->save();
    }

    public static function product_features($check = null)
    {
        if ($check !== null) {
            if (self::$product_features === null) {
                $list = \Mktr\Model\Config::db()->query('SELECT `feature_id`,`description` FROM `?:product_features_descriptions` WHERE lang_code ="' . CART_LANGUAGE . '"')->fetch_all(MYSQLI_ASSOC);
                self::$product_features = [];

                foreach ($list as $k => $v) {
                    self::$product_features[$v['feature_id']] = $v['description'];
                }

                $list = \Mktr\Model\Config::db()->query('SELECT `option_id`,`option_name`,`internal_option_name` FROM `?:product_options_descriptions` WHERE lang_code ="' . CART_LANGUAGE . '"')->fetch_all(MYSQLI_ASSOC);
                foreach ($list as $k => $v) {
                    self::$product_features[$v['option_id']] = empty($v['internal_option_name']) ? $v['option_name'] : $v['internal_option_name'];
                }
            }
            $key = array_search($check, self::$product_features);
            if ($key !== false) {
                return $key;
            }
        }

        return 0;
    }

    protected function cast($key, $value)
    {
        switch (self::CONFIG_DATA[$key]['type']) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return (object) $value;
            case 'array':
            case 'json':
                return (array) $value;
            case 'date':
            case 'datetime':
                return new \DateTime($value);
            case 'timestamp':
                return $value;
            default:
                return $value;
        }
    }
}
