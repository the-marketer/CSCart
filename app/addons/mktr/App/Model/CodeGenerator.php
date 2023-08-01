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

use Mktr\Helper\Valid;

class CodeGenerator
{
    const PREFIX = 'MKTR-';
    const LENGTH = 10;
    const DESCRIPTION = 'Discount Code Generated through TheMarketer API';
    const SYMBOLS_COLLECTION = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    private static $i = null;

    protected $attributes = [];
    protected $code = null;
    protected $data = [];
    protected $build = [];

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
        if (method_exists($i, $name)) {
            return call_user_func_array([$i, $name], $arguments);
        } else {
            if (DEVELOPMENT) {
                throw new \Exception("Static method {$name} does not exist.");
            }

            return null;
        }
    }

    public function __get($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = null;
        }

        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public static function i()
    {
        if (self::$i == null) {
            self::$i = new self();
        }

        return self::$i;
    }

    public static function getPromo($name = null)
    {
        $promo = fn_get_promotions();
    }

    protected function save()
    {
        $b = [];
        $pro = Config::db()->
            query('SELECT * FROM ?:promotion_descriptions WHERE `name` = "?p" ORDER BY promotion_id DESC LIMIT 1', $this->name)->
            fetch_all(MYSQLI_ASSOC);

        $this->promotion_id = '0';
        $this->data = [];

        if (!empty($pro)) {
            $check = fn_get_promotions(['promotion_id' => $pro[0]['promotion_id'], 'items_per_page' => 1]);
            $pro = end($check[0]);

            if ($pro !== false && $pro['from_date'] == $this->from_date->getTimestamp()) {
                $this->promotion_id = $pro['promotion_id'];
                $this->data = $pro;
                $this->data['conditions'] = unserialize($this->data['conditions']);
                $this->data['bonuses'] = unserialize($this->data['bonuses']);
            }
        }

        if ($this->promotion_id == '0') {
            $b['bonuses'] = [];
            $b['zone'] = 'cart';
            $b['name'] = $this->name;
            $b['detailed_description'] = $this->description;
            $b['short_description'] = $this->description;
            $b['stop_other_rules'] = 'N';
            $b['priority'] = 1;

            $b['conditions'] = [
                'set' => 'all',
                'set_value' => '1',
                'conditions' => [
                    [
                        'condition' => 'once_per_customer',
                        'operator' => 'eq',
                        'value' => 'Y',
                    ], [
                        'set' => 'any',
                        'set_value' => '1',
                        'conditions' => [],
                    ],
                ],
            ];

            $b['to_date'] = is_object($this->to_date) ? $this->to_date->getTimestamp() : $this->to_date;
            $b['from_date'] = $this->from_date->getTimestamp();

            if ($this->bonus != null) {
                $b['bonuses'][] = [
                    'bonus' => $this->bonus,
                    'value' => $this->value,
                ];
            }

            if ($this->discount_bonus != null) {
                $b['bonuses'][] = [
                    'bonus' => 'order_discount',
                    'discount_bonus' => $this->discount_bonus,
                    'discount_value' => $this->discount_value,
                ];
            }
        } else {
            $b['conditions'] = $this->data['conditions'];
            $b['bonuses'] = $this->data['bonuses'];
            $b['zone'] = $this->data['zone'];
            $b['name'] = $this->data['name'];
            $b['detailed_description'] = $this->data['detailed_description'];
            $b['short_description'] = $this->data['short_description'];
            $b['stop_other_rules'] = $this->data['stop_other_rules'];
            $b['to_date'] = $this->data['to_date'];
            $b['from_date'] = $this->data['from_date'];
        }
        $b['status'] = 'A';
        $b['conditions']['conditions'][1]['conditions'][] = [
            'operator' => 'eq',
            'condition' => 'coupon_code',
            'value' => $this->coupon_code,
        ];

        $this->build = $b;
        fn_update_promotion($this->build, $this->promotion_id);
    }

    protected function checkCode()
    {
        $pro = Config::db()->
            query(
                'SELECT * FROM ?:promotions WHERE `conditions_hash` LIKE "?p" ORDER BY promotion_id DESC LIMIT 1',
                '%coupon_code=' . $this->code . '%'
            )->fetch_all(MYSQLI_ASSOC);

        return !empty($pro);
    }

    protected function newCode()
    {
        $this->code = self::PREFIX;
        for ($i = 0, $indexMax = strlen(self::SYMBOLS_COLLECTION) - 1; $i < self::LENGTH; ++$i) {
            $this->code .= substr(self::SYMBOLS_COLLECTION, \rand(0, $indexMax), 1);
        }
        if ($this->checkCode()) {
            return $this->newCode();
        }

        return $this->code;
    }

    public static function getNewCode()
    {
        $coupon = self::i();
        $type = Valid::getParam('type', null);
        $value = Valid::getParam('value', null);
        $expiration = Valid::getParam('expiration_date', null);

        $rules = [0 => 'Fixed', 1 => 'Percent', 2 => 'FreeShipping'];

        switch ($type) {
            case 0: /* "fixed_cart" */
                $coupon->discount_bonus = 'by_fixed';
                $coupon->discount_value = $value;
                break;
            case 1: /* "percent" */
                $coupon->discount_bonus = 'by_percentage';
                $coupon->discount_value = $value;
                break;
            case 2: /* "free_shipping" */
                $coupon->bonus = 'free_shipping';
                $coupon->value = 1;
                if ($value != 0) {
                    $coupon->discount_bonus = 'by_percentage';
                    $coupon->discount_value = $value;
                }
                break;
        }

        if ($expiration !== null) {
            $coupon->to_date = (new \DateTime($expiration))->setTime(23, 59, 59);
        } else {
            $coupon->to_date = 0;
            /* 1 year */
        }
        $coupon->from_date = (new \DateTime())->modify('-1 day')->setTime(0, 0, 0);
        $coupon->name = 'Themarketer-' . $rules[$type] . '-' . $value . ($expiration === null ? '' : '-' . $expiration);
        $coupon->description = self::DESCRIPTION . ' (' . $rules[$type] . '-' . $value . ($expiration === null ? '' : '-' . $expiration) . ')';

        /* need To Be Last */
        $coupon->coupon_code = $coupon->newCode();
        $coupon->save();

        return $coupon->coupon_code;
    }
}
