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

use Mktr\Helper\DataBase;

class Orders extends DataBase
{
    protected $attributes = [
        'order_no' => null,
        'order_status' => null,
        'refund_value' => null,
        'created_at' => null,
        'email_address' => null,
        'phone' => null,
        'firstname' => null,
        'lastname' => null,
        'city' => null,
        'county' => null,
        'address' => null,
        'discount_value' => null,
        'discount_code' => null,
        'shipping' => null,
        'tax' => null,
        'total_value' => null,
        'products' => null,
    ];

    protected $ref = [
        'order_no' => 'order_id',
        'number' => 'order_id',
        'order_status' => 'getStatus',
        'current_state' => 'status',
        'id_customer' => 'user_id',
        'id_address_invoice' => 'id_address_invoice',
        'refund_value' => 'getRefund',
        'created_at' => 'getTime',
        'email_address' => 'email',
        'phone' => 'getPhone',
        'firstname' => 'getFirstName',
        'lastname' => 'getLastName',
        'city' => 'getCity',
        'county' => 'getCounty',
        'address' => 'getAddress',
        'discount_value' => 'getDiscountValue',
        'discount_code' => 'getDiscountCode',
        'shipping' => 'shipping_cost',
        'total_discounts' => 'total_discounts',
        'tax' => 'getTax',
        'total_value' => 'total',
        'products' => 'getProductsData',
        'products_api' => 'getProducts',
    ];

    protected $cast = [
        'tax' => 'double',
        'shipping' => 'double',
        'total_value' => 'double',
        'discount_value' => 'double',
        'created_at' => 'date',
    ];

    protected $functions = [
        'getStatus',
        'getRefund',
        'getPhone',
        'getFirstName',
        'getLastName',
        'getCity',
        'getCounty',
        'getAddress',
        'getTax',
        'getProductsData',
        'getProducts',
        'getDiscountValue',
        'getDiscountCode',
        'getTime',
    ];

    protected $vars = [];

    protected $orderBy = 'id_order';
    protected $direction = 'ASC';
    protected $dateFormat = 'Y-m-d H:i';

    protected $refund = 0;

    private static $i = null;
    private static $curent = null;
    private static $d = [];
    private static $ll = [];

    private static $shop = null;
    private static $orderState = null;
    private static $customerData = [];
    private static $adressData = [];

    public static function i()
    {
        if (self::$i === null) {
            self::$i = new static();
        }

        return self::$i;
    }

    public static function c()
    {
        return self::$curent;
    }

    public static function orderState($ID)
    {
        if (self::$orderState === null) {
            self::$orderState = fn_get_statuses(STATUSES_ORDER);
        }

        return array_key_exists($ID, self::$orderState) ? self::$orderState[$ID]['description'] : 'unknown';
    }

    public static function getPage($num = 1, $limit = null)
    {
        return self::getPageByDate($num, null, null, $limit);
    }

    public static function getPageByDate($num = 1, $start_date = null, $end_date = null, $limit = null)
    {
        $i = self::i();

        if ($limit === null) {
            $limit = $i->limit;
        }

        if ($num === null) {
            $num = 1;
        }

        $arg = [
            'page' => $num,
            'items_per_page' => $limit,
        ];

        if ($start_date !== null || $end_date !== null) {
            if ($start_date !== null) {
                $arg['time_from'] = strtotime($start_date);
            }
            $arg['period'] = 'T';
            $arg['time_to'] = $end_date !== null ? strtotime($end_date) : TIME;
        }

        $list = fn_get_orders($arg);

        if ($list[1]['page'] == $num) {
            self::$ll = $list[0];

            return self::$ll;
        }

        return [];
    }

    public static function getByID($id, $new = false)
    {
        if ($new || !array_key_exists($id, self::$d)) {
            self::$d[$id] = new static();
            self::$d[$id]->data = fn_get_order_info($id);
        }

        self::$curent = self::$d[$id];

        return self::$curent;
    }

    protected function getStatus()
    {
        return self::orderState($this->current_state);
    }

    protected function getFirstName()
    {
        if (!empty($this->data['firstname'])) {
            return $this->data['firstname'];
        } elseif (!empty($this->data['b_firstname'])) {
            return $this->data['b_firstname'];
        } elseif (!empty($this->data['s_firstname'])) {
            return $this->data['s_firstname'];
        }

        return '';
    }

    protected function getLastName()
    {
        if (!empty($this->data['lastname'])) {
            return $this->data['lastname'];
        } elseif (!empty($this->data['b_lastname'])) {
            return $this->data['b_lastname'];
        } elseif (!empty($this->data['s_lastname'])) {
            return $this->data['s_lastname'];
        }

        return '';
    }

    protected function getPhone()
    {
        $phone = null;
        if (!empty($this->data['phone'])) {
            $phone = $this->data['phone'];
        } elseif (!empty($this->data['b_phone'])) {
            $phone = $this->data['b_phone'];
        } elseif (!empty($this->data['s_phone'])) {
            $phone = $this->data['s_phone'];
        }
        if ($phone != null) {
            $phone = \Mktr\Helper\Valid::validateTelephone($phone);
        }

        return $phone;
    }

    protected function getCity()
    {
        if (!empty($this->data['b_city'])) {
            return $this->data['b_city'];
        } elseif (!empty($this->data['s_city'])) {
            return $this->data['s_city'];
        }

        return '';
    }

    protected function getCounty()
    {
        if (!empty($this->data['b_county'])) {
            return $this->data['b_county'];
        } elseif (!empty($this->data['s_county'])) {
            return $this->data['s_county'];
        } elseif (!empty($this->data['b_country'])) {
            return $this->data['b_country'];
        } elseif (!empty($this->data['s_country'])) {
            return $this->data['s_country'];
        }

        return '';
    }

    protected function getAddress()
    {
        $adr = [];

        if (!empty($this->data['b_address'])) {
            $adr[] = $this->data['b_address'];
        } elseif (!empty($this->data['s_address'])) {
            $adr[] = $this->data['s_address'];
        }
        if (!empty($this->data['b_address_2'])) {
            $adr[] = $this->data['b_address_2'];
        } elseif (!empty($this->data['s_address_2'])) {
            $adr[] = $this->data['s_address_2'];
        }

        return implode(' ', $adr);
    }

    protected function getRefund()
    {
        return $this->order_status == 'Refund' ? $this->total : 0;
    }

    protected function getTax()
    {
        if (!empty($this->data['taxes'])) {
            $tax = 0;
            foreach ($this->data['taxes'] as $key => $value) {
                $tax += $value['tax_subtotal'];
            }

            return $tax;
        }

        return $this->tax_subtotal;
    }

    protected function getPricesAfterPromo($price = 0, $promo = [])
    {
        foreach ($promo as $k => $v) {
            foreach ($v['bonuses'] as $k1 => $v1) {
                if ($v1['discount_bonus'] == 'by_percentage') {
                    $price -= ($price * ($v1['discount_value'] / 100));
                } else {
                    $price -= $v1['discount_value'];
                }
            }
        }

        return $price;
    }

    protected function getProductsData()
    {
        $i = 0;
        $products = [];
        foreach ($this->data['products'] as $p) {
            $pp = Product::getByID($p['product_id'], true);
            if ($pp->id === null) {
                continue;
            }
            $tmp = [];
            $products[$i]['product_id'] = $pp->id;
            $products[$i]['sku'] = $pp->sku;
            $products[$i]['name'] = $pp->name;
            $products[$i]['url'] = $pp->url;
            $products[$i]['main_image'] = $pp->main_image;
            $products[$i]['category'] = $pp->category;
            $products[$i]['brand'] = $pp->brand;
            $products[$i]['quantity'] = $p['amount'];

            // $price = $p['original_price'];
            // $tmp['price'] = $price;
            $tmp['sale_price'] = $p['original_price'];
            if (!isset($p['promotions'])) {
                $p['promotions'] = [];
            }
            $promoPrice = $this->getPricesAfterPromo($tmp['sale_price'], $p['promotions']);
            /*
                        $tmp['price'] = $tmp['price'] <= 0 && $tmp['sale_price'] >= 0 ? $tmp['sale_price'] : $tmp['price'];
                        $tmp['sale_price'] = $tmp['sale_price'] <= 0 ? $tmp['price'] : $tmp['sale_price'];

                        $tmp['price'] = max($tmp['sale_price'], $tmp['price']);
            */
            // $products[$i]['price'] = $this->toDigit($p['amount'] * $tmp['sale_price']);
            // $products[$i]['sale_price'] = $this->toDigit($tmp['sale_price']);

            // $products[$i]['price'] = $this->toDigit($p['amount'] * $promoPrice);

            $products[$i]['price'] = $this->toDigit($p['original_price']);
            $products[$i]['sale_price'] = $this->toDigit($promoPrice);

            $newVariation = [
                'id' => [$pp->id],
                'sku' => [$pp->sku],
            ];

            if (!empty($p['variation_features'])) {
                foreach ($p['variation_features'] as $val0) {
                    $newVariation['id'][] = $val0['feature_id'];
                    $newVariation['id'][] = $val0['variant_id'];
                    $newVariation['sku'][] = $val0['variant'];
                }
            }

            if (!empty($p['product_options'])) {
                foreach ($p['product_options'] as $val0) {
                    $newVariation['id'][] = $val0['option_id'];
                    $newVariation['id'][] = $val0['value'];
                    $newVariation['sku'][] = $val0['variant_name'];
                }
            }
            $products[$i]['variation_id'] = str_replace(' ', '_', implode('_', $newVariation['id']));
            $products[$i]['variation_sku'] = str_replace(' ', '_', implode('_', $newVariation['sku']));
            ++$i;
        }

        return $products;
    }

    protected function getProducts()
    {
        $i = 0;
        $products = [];
        foreach ($this->data['products'] as $p) {
            $pp = Product::getByID($p['product_id'], true);
            $products[$i]['product_id'] = $pp->id;
            $products[$i]['quantity'] = $p['amount'];

            // $products[$i]['price'] = $p['amount'] * $p['original_price'];

            $products[$i]['price'] = $p['original_price'];

            $newVariation = ['sku' => [$pp->sku]];

            if (!empty($p['variation_features'])) {
                foreach ($p['variation_features'] as $val0) {
                    $newVariation['sku'][] = $val0['variant'];
                }
            }
            if (!empty($p['product_options'])) {
                foreach ($p['product_options'] as $val0) {
                    $newVariation['sku'][] = $val0['variant_name'];
                }
            }
            $products[$i]['variation_sku'] = str_replace(' ', '_', implode('_', $newVariation['sku']));
            ++$i;
        }

        return $products;
    }

    protected function getDiscountValue()
    {
        return empty($this->coupons) ? 0 : $this->subtotal_discount;
    }

    protected function getDiscountCode()
    {
        $discounts = $this->coupons;
        // $discounts = $this->promotions;
        // dd($discounts, $this->promotions, $this->data);
        $d = '';

        if (!empty($discounts)) {
            $discountCode = [];
            foreach ($discounts as $code => $discount) {
                $discountCode[] = $code;
            }
            $d = implode('|', $discountCode);
        }

        return $d;
    }

    protected function toEvent($json = false)
    {
        $out = [];

        foreach (['number', 'email_address', 'phone', 'firstname', 'lastname', 'city', 'county', 'address',
            'discount_value', 'discount_code', 'shipping', 'tax', 'total_value', 'products'] as $v) {
            $out[$v] = $this->{$v};
        }

        return $json ? \Mktr\Helper\Valid::toJson($out) : $out;
    }

    protected function toArrayFeed()
    {
        $list = $this->toArray();
        if (empty($list['products'])) {
            return null;
        }

        return $list;
    }

    protected function toApi()
    {
        $out = [];

        foreach ([
            'number', 'email_address', 'phone', 'firstname', 'lastname', 'city', 'county', 'address',
            'discount_value', 'discount_code', 'shipping', 'tax', 'total_value', 'products_api',
        ] as $v) {
            if ($v === 'products_api') {
                $out['products'] = $this->{$v};
            } else {
                $out[$v] = $this->{$v};
            }
        }

        return $out;
    }
}
