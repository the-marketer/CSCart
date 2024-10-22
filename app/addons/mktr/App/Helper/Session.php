<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Helper;

use Mktr\Model\Config;

class Session
{
    private static $i = null;
    private static $uid = null;
    private static $MKTR_TABLE = null;

    private $data = [];
    private $org = [];
    public $insert = true;

    private $isDirty = false;

    public static function i()
    {
        if (self::$i == null) {
            self::$MKTR_TABLE = '?:mktr';
            self::$i = new self();
        }

        return self::$i;
    }

    public static function data()
    {
        return self::i()->data;
    }

    public function remove($key)
    {
        if ($this->data[$key]) {
            unset($this->data[$key]);
        }

        return $this;
    }

    public static function getUid()
    {
        if (self::$uid === null) {
            if (array_key_exists('smuid', $_GET) && !empty($_GET['smuid'])) {
                self::$uid = $_GET['smuid'];
            } elseif (!isset(\Tygh\Tygh::$app['session']['__sm__uid'])) {
                self::$uid = uniqid();
                \Tygh\Tygh::$app['session']['__sm__uid'] = self::$uid;
            } else {
                self::$uid = \Tygh\Tygh::$app['session']['__sm__uid'];
            }
        }

        return self::$uid;
    }

    public function __construct()
    {
        $uid = self::getUid();
        $data = Config::db()->getField('SELECT `data` FROM `' . self::$MKTR_TABLE . '` WHERE `uid` = "?p"', $uid);
        $this->org = [];
        if (is_array($data)) {
            if (array_key_exists(0, $data) && isset($data[0]['data'])) {
                if ($data[0]['data'] != '') {
                    $this->org = unserialize($data[0]['data']);
                    $this->insert = false;
                }
            }
        } else {
            if ($data != '') {
                $this->insert = false;
                $this->org = unserialize($data);
            }
        }
        $this->data = $this->org;
    }

    public static function set($key, $value = null)
    {
        if ($value === null) {
            self::i()->remove($key);
        } else {
            self::i()->data[$key] = $value;
        }

        self::i()->isDirty = true;
    }

    public static function get($key, $default = null)
    {
        if (isset(self::i()->data[$key])) {
            return self::i()->data[$key];
        } else {
            return $default;
        }
    }

    public static function save()
    {
        if (self::i()->isDirty) {
            $uid = self::getUid();
            $table_name = self::$MKTR_TABLE;

            if (!empty(self::i()->data)) {
                $data = [
                    'data' => serialize(self::i()->data),
                    'expire' => date('Y-m-d H:i:s', strtotime('+2 day')),
                ];
                if (self::i()->insert) {
                    $data['uid'] = $uid;
                    try {
                        Config::db()->query('INSERT INTO `' . self::$MKTR_TABLE . '` ?e', $data);
                    } catch (\Exception $e) {
                        Config::db()->query('UPDATE `' . self::$MKTR_TABLE . '` SET ?u WHERE `uid` = ?i', $data, $uid);
                    }
                } else {
                    Config::db()->query('UPDATE `' . self::$MKTR_TABLE . '` SET ?u WHERE `uid` = ?i', $data, $uid);
                }
            // if (count(self::i()->org) > 0) {
            //    Config::db()->query('UPDATE `' . self::$MKTR_TABLE . '` SET ?u WHERE `uid` = ?i', $data, $uid);
            // }
            } else {
                Config::db()->query('DELETE FROM `' . self::$MKTR_TABLE . '` WHERE uid = ?i', $uid);
            }

            self::clearIfExipire();
            self::i()->isDirty = false;

            return true;
        }

        return false;
    }

    public static function clearIfExipire()
    {
        $expire_at = date('Y-m-d H:i:s', time());
        Config::db()->query('DELETE FROM `' . self::$MKTR_TABLE . '` WHERE `expire` < ?i', $expire_at);
    }

    public static function clear()
    {
        self::i()->data = [];
        self::i()->isDirty = true;
    }

    public function __destruct()
    {
        if ($this->isDirty) {
            $this->save();
        }
    }

    public static function addToWishlist($pId, $pAttr)
    {
        self::sessionSet('add_to_wish_list', [$pId, $pAttr]);
    }

    public static function removeFromWishlist($pId, $pAttr)
    {
        self::sessionSet('remove_from_wishlist', [$pId, $pAttr]);
    }

    public static function addToCart($pId, $pAttr, $qty)
    {
        $qty = $qty <= 0 ? 1 : $qty;
        self::sessionSet('add_to_cart', [$pId, $pAttr, $qty]);
    }

    public static function removeFromCart($pId, $pAttr, $qty)
    {
        $qty = $qty <= 0 ? 1 : $qty;
        self::sessionSet('remove_from_cart', [$pId, $pAttr, $qty]);
    }

    public static function setEmail($email)
    {
        self::sessionSet('set_email', $email);
    }

    public static function setPhone($phone)
    {
        self::sessionSet('set_phone', $phone);
    }

    public static function sessionSet($name, $data, $key = null)
    {
        $add = self::get($name);

        if ($key === null) {
            $n = '';
            for ($i = 0; $i < 5; ++$i) {
                $n .= \rand(0, 9);
            }
            $add[time() . $n] = $data;
        } else {
            $add[$key] = $data;
        }

        self::set($name, $add);
    }
}
