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

class Product extends DataBase
{
    protected $attributes = [
        'id' => null,
        'sku' => null,
        'name' => null,
        'description' => null,
        'url' => null,
        'main_image' => null,
        'category' => null,
        'brand' => null,
        'acquisition_price' => null,
        'price' => null,
        'sale_price' => null,
        // 'special_price' => null,
        'sale_price_start_date' => null,
        'sale_price_end_date' => null,
        'availability' => null,
        'stock' => null,
        'media_gallery' => null,
        'variations' => null,
        'created_at' => null,
    ];

    protected $ref = [
        'id' => 'product_id',
        'sku' => 'getSku',
        'name' => 'product',
        'description' => 'full_description',
        'url' => 'getUrl',
        'main_image' => 'getMainImage',
        'category' => 'getCategory',
        'brand' => 'getBrand',
        'acquisition_price' => 'getAcquisitionPrice',
        'price' => 'getPrice',
        'regular_price' => 'price',
        'sale_price' => 'getSalePrice',
        'sale_price_start_date' => 'getSalePriceStartDate',
        'sale_price_end_date' => 'getSalePriceEndDate',
        'availability' => 'getAvailability',
        'stock' => 'getStock',
        'media_gallery' => 'getMediaGallery',
        'variations' => 'variation',
        'variation' => 'getVariation',
        'created_at' => 'getTime',
    ];

    protected $functions = [
        'getSku',
        'getUrl',
        'getMainImage',
        'getCategory',
        'getBrand',
        'getAcquisitionPrice',
        'getPrice',
        'getSalePrice',
        'getSalePriceStartDate',
        'getSalePriceEndDate',
        'getAvailability',
        'getStock',
        'getMediaGallery',
        'getVariation',
        'getTime',
    ];

    protected $vars = [
        'variation',
    ];

    protected $cast = [
        'sale_price_start_date' => 'date',
        'sale_price_end_date' => 'date',
        'created_at' => 'date',
        'acquisition_price' => 'double',
    ];

    protected $orderBy = 'product_id';
    protected $direction = 'ASC';
    protected $dateFormat = 'Y-m-d H:i';
    protected $hide = ['variation', 'main_pair'];

    private static $i = null;
    private static $curent = null;
    private static $d = [];
    private static $ll = [];
    private static $defStock = null;
    private static $att = null;

    protected $realStock = 0;
    protected $isCombination = null;
    protected $img = null;
    protected $prices = null;
    protected $pricesDate = null;
    protected $reference = null;
    protected $var = null;
    protected $varData = null;
    protected $variant = [];
    protected $optionList = [];
    protected $variants = [[]];
    protected $getDefault = null;

    const TYPE_COMBINATION = 'combinations';

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

    public static function getDefaultStock()
    {
        if (self::$defStock === null) {
            self::$defStock = Config::i()->default_stock;
        }

        return self::$defStock;
    }

    public static function getPage($num = 1, $limit = null)
    {
        $i = self::i();

        if ($limit === null) {
            $limit = $i->limit;
        }

        if ($num === null) {
            $num = 1;
        }

        $list = fn_get_products([
            'page' => $num,
            'status' => 'A',
            'use_caching' => false,
            // 'storefront' => \Mktr\Model\Config::shop(),
            // 'area' => 'C',
            // 'custom_extend' => [ 'product_id', 'product_name' ]
        ], $limit);

        if ($list[1]['page'] == $num) {
            self::$ll = $list[0];

            return self::$ll;
        }

        return [];
    }

    public static function getByIDNoParent($id)
    {
        $prod = fn_get_product_data($id, \Tygh\Tygh::$app['session']['auth']);

        return $prod;
    }

    public static function clear()
    {
        if (self::$i !== null) {
            self::$i = null;
            self::$curent = null;
            self::$d = [];
            self::$ll = [];
            self::$defStock = null;
            self::$att = null;
        }
    }

    public static function getByID($id, $full = false, $new = false)
    {
        $id = (int) $id;
        if ($new || !array_key_exists($id, self::$d)) {
            self::$d[$id] = new static();

            $prod = self::getByIDNoParent($id);

            if ($prod !== false) {
                if ($full) {
                    fn_gather_additional_products_data($prod, [
                        'get_icon' => true,
                        'get_detailed' => true,
                        'get_discounts' => true,
                        'get_additional' => true,
                        'get_extra' => true,
                        'get_for_one_product' => true,
                    ]);
                }
                self::$d[$id]->data = $prod;
            } else {
                self::$d[$id]->data = null;
            }
        }
        self::$curent = self::$d[$id];

        return self::$curent;
    }

    public static function getQty($id_product, $id_product_attribute, $cart_id)
    {
        $i = self::i();

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'cart_product`' .
        ' WHERE `id_cart` = "' . $cart_id . '" AND `id_product_attribute` = "' . (int) $id_product_attribute . '" AND `id_product` = "' . $id_product . '"';

        return Config::db()->executeS($sql);
    }

    protected function getSku()
    {
        return $this->product_code ? $this->product_code : $this->id;
    }

    protected function getUrl()
    {
        return fn_url('products.view?product_id=' . $this->id . '&storefront_id=' . Config::shop(), 'C');
    }

    protected function getMainImage()
    {
        return isset($this->main_pair['detailed']['image_path']) ? $this->main_pair['detailed']['image_path'] : null;
    }

    protected function getMediaGallery()
    {
        $i['image'] = [];

        foreach ($this->image_pairs as $value) {
            $i['image'][] = $value['detailed']['image_path'];
        }

        if (empty($i['image'])) {
            $i['image'] = [$this->main_image];
        }

        return $i;
    }

    protected function getAcquisitionPrice()
    {
        return 0;
    }

    protected function getPrice()
    {
        return $this->getPricesNow('price');
    }

    protected function getSalePrice()
    {
        return $this->getPricesNow('sale_price');
    }

    protected function getSalePriceStartDate()
    {
        return $this->getSalePriceDateNow('sale_price_start_date');
    }

    protected function getSalePriceEndDate()
    {
        return $this->getSalePriceDateNow('sale_price_end_date');
    }

    protected function isSize($toCheck)
    {
        return $this->checkNow('size', $toCheck);
    }

    protected function isColor($toCheck)
    {
        return $this->checkNow('color', $toCheck);
    }

    protected function getCategory()
    {
        return Category::getByID($this->main_category)->hierarchy;
    }

    protected function getBrand()
    {
        if (is_array($this->header_features)) {
            foreach ($this->header_features as $v) {
                if ($this->checkNow('brand', $v['feature_id'])) {
                    return $v['variants'][$v['variant_id']]['variant'];
                }
            }
        }

        return 'N/A';
    }

    protected function getPricesNow($witch = null)
    {
        if ($this->prices === null) {
            if (!isset($this->data['discount'])) {
                $this->data['discount'] = 0;
            }

            if (isset($this->data['original_price']) && $this->data['original_price'] > 0) {
                $sale_price = $this->data['original_price'];
            } else {
                $sale_price = $this->data['base_price'];
            }
            if (isset($this->data['list_price']) && $this->data['list_price'] > 0) {
                $price = $this->data['list_price'];
            } else {
                $price = $sale_price;
            }

            $p['price'] = $this->toDigit($price);
            $p['sale_price'] = $this->toDigit($sale_price);

            $p['sale_price'] = $this->getPricesAfterPromo($p['sale_price']);

            $p['price'] = $p['price'] <= 0 && $p['sale_price'] >= 0 ? $p['sale_price'] : $p['price'];
            $p['sale_price'] = $p['sale_price'] <= 0 ? $p['price'] : $p['sale_price'];

            $p['price'] = max($p['sale_price'], $p['price']);
            $this->prices = $p;
        }

        return $witch === null ? null : $this->prices[$witch];
    }

    protected function getSalePriceDateNow($witch = null)
    {
        if ($this->pricesDate === null) {
            $pricesDate['sale_price_start_date'] = 0;
            $pricesDate['sale_price_end_date'] = 0;

            $pc = null;

            if (isset($this->data['promotions'])) {
                foreach ($this->promotions as $k => $v) {
                    foreach ($v['bonuses'] as $k1 => $v1) {
                        if ($this->discount == $v1['discount']) {
                            $pc = $k;
                            break;
                        }
                    }
                    if ($pc !== null) {
                        break;
                    }
                }
            }

            if ($pc !== null) {
                $promotion = fn_get_promotion_data($pc);
                $pricesDate['sale_price_start_date'] = $promotion['from_date'];
                $pricesDate['sale_price_end_date'] = $promotion['to_date'];
            }

            if ($pricesDate['sale_price_end_date'] != 0) {
                $pricesDate['sale_price_start_date'] = \DateTime::createFromFormat('U', $pricesDate['sale_price_start_date']);
                $pricesDate['sale_price_end_date'] = \DateTime::createFromFormat('U', $pricesDate['sale_price_end_date']);
            } else {
                $pricesDate['sale_price_start_date'] = null;
                $pricesDate['sale_price_end_date'] = null;
            }
            $this->pricesDate = $pricesDate;
        }

        return $witch === null ? null : $this->pricesDate[$witch];
    }

    protected function checkNow($type, $toCheck)
    {
        if (self::$att === null) {
            self::$att = [
                'color' => Config::i()->color,
                'size' => Config::i()->size,
                'brand' => Config::i()->brand,
            ];
        }

        return in_array(strtolower($toCheck), self::$att[$type]);
    }

    protected function getAvailability($qty = null)
    {
        $qty = isset($qty) ? $qty : $this->amount;
        if ($qty < 0) {
            $availability = self::getDefaultStock();
        } elseif ($qty == 0) {
            /** @noinspection PhpUnnecessaryBoolCastInspection */
            $availability = $this->data['out_of_stock_actions'] === 'B' ? 2 : 0;
        } else {
            $availability = 1;
        }

        return $availability;
    }

    protected function isCombination()
    {
        if ($this->isCombination === null) {
            $is = true;

            if (empty($this->product_features)) {
                if ($is) {
                    $this->isCombination = [];
                }
                $this->isCombination[] = 'product_features';
                $is = false;
            }

            if (empty($this->product_options)) {
                if ($is) {
                    $this->isCombination = [];
                }
                $this->isCombination[] = 'product_options';
                $is = false;
            }

            if ($is) {
                $this->isCombination = false;
            }
        }

        return $this->isCombination;
    }

    protected function getStock()
    {
        if ($this->isCombination()) {
            $this->getVariation();
        }

        return max($this->realStock, $this->amount);
    }

    protected function buildProducts($key1 = null, $prevKey = null, $isFeatures = false)
    {
        if ($key1 === null && $prevKey === null) {
            $this->optionList = [[]];
            $this->variants = [[]];

            if ($this->isCombination()) {
                $opt = 'variants';
                $newList = [];
                foreach ($this->isCombination() as $ComKey) {
                    if ($ComKey === 'product_options') {
                        $list = $this->product_options;
                    } else {
                        $list = $this->product_features;
                    }
                    if ($list != null) {
                        $newList = array_merge($newList, $list);
                    }
                }

                foreach ($newList as $val) {
                    if (array_key_exists('option_id', $val)) {
                        $isFeatures = false;
                        // $list = $this->product_options;
                        $this->optionList[1] = 'option_id';
                    } else {
                        $isFeatures = true;
                        // $list = $this->product_features;
                        $this->optionList[1] = 'feature_id';

                        if ($this->getDefault !== null && isset($val['variant_id'])) {
                            $this->getDefault[$val['feature_id']] = $val['variant_id'];
                        }
                    }

                    $id = $val[$this->optionList[1]];

                    $this->optionList[0][$id] = $val;

                    unset($this->optionList[0][$id][$opt]);

                    if (!empty($val[$opt]) && is_array($val[$opt])) {
                        $products = $this->buildProducts($val[$opt], $this->variants, $isFeatures);
                        $this->variants = $products;
                    }
                }
            }

            return $this->variants;
        } else {
            $products = [];
            foreach ($prevKey as $prevArray) {
                foreach ($key1 as $cValue) {
                    if ($isFeatures) {
                        $cValue['variant_name'] = $cValue['variant'];
                        $cValue['option_id'] = $cValue['feature_id'];
                        $cValue['modifier'] = '0.000';
                        $cValue['modifier_type'] = 'A';
                    }
                    $prevArrayNew = array_merge($prevArray, [$cValue]);
                    $products[] = $prevArrayNew;
                }
            }

            return $products;
        }
    }

    protected function getPricesVarNow($witch = null)
    {
        if ($this->pricesVar === null) {
            if (isset($this->data['original_price']) && $this->data['original_price'] > 0) {
                $sale_price = $this->data['original_price'];
            } else {
                $sale_price = $this->data['base_price'];
            }
            if (isset($this->data['list_price']) && $this->data['list_price'] > 0) {
                $price = $this->data['list_price'];
            } else {
                $price = $sale_price;
            }

            $p['price'] = $this->toDigit($price);
            $p['sale_price'] = $this->toDigit($sale_price);

            $p['sale_price'] = $this->getPricesAfterPromo($p['sale_price']);

            $p['price'] = $p['price'] <= 0 && $p['sale_price'] >= 0 ? $p['sale_price'] : $p['price'];
            $p['sale_price'] = $p['sale_price'] <= 0 ? $p['price'] : $p['sale_price'];

            $p['price'] = max($p['sale_price'], $p['price']);
            $this->pricesVar = $p;
        }

        return $witch === null ? null : $this->pricesVar[$witch];
    }

    protected function getPricesAfterPromo($price = 0)
    {
        if (!empty($this->data['promotions'])) {
            foreach ($this->data['promotions'] as $k => $v) {
                foreach ($v['bonuses'] as $k1 => $v1) {
                    if ($v1['discount_bonus'] == 'by_percentage') {
                        $price -= ($price * ($v1['discount_value'] / 100));
                    } else {
                        $price -= $v1['discount_value'];
                    }
                }
            }
        }

        return $price;
    }

    public static function getProductFromCartData($cart)
    {
        $pID = $cart['product_id'];
        $pro = self::getByID($pID, true);

        if (!empty($cart['product_features']) || !empty($cart['product_options'])) {
            $vID = [$pID];
            foreach (['product_features', 'product_options'] as $val) {
                if (array_key_exists($val, $cart) && is_array($cart[$val])) {
                    foreach ($cart[$val] as $k => $v) {
                        $vID[] = $k . '_' . $v;
                    }
                }
            }
            $vID = implode('_', $vID);

            return ['pId' => $pID, 'pAttr' => $vID];
        }

        return ['pId' => $pID, 'pAttr' => null];
    }

    public static function getProductVariant($pID, $vID)
    {
        $pro = self::getByID($pID, true);
        if ($vID !== null) {
            $vv = $pro->getVariantData();
            if ($vv != null) {
                if (array_key_exists($vID, $vv)) {
                    return $vv[$vID];
                } else {
                    $vID2 = explode('_', $vID, 2);
                    $toAdd = [];
                    foreach ($pro->getDefault as $k => $v) {
                        if (strpos($vID2[1], $k . '_' . $v) === false) {
                            $toAdd[] = $k;
                            $toAdd[] = $v;
                            // $vID2[1] = $k.'_'.$v.'_'.$vID2[1];
                        }
                    }
                    $vID2[1] = implode('_', $toAdd) . '_' . $vID2[1];
                    $vID2 = implode('_', $vID2);
                    // dd($vID2, $vv);
                    if (array_key_exists($vID2, $vv)) {
                        return $vv[$vID2];
                    }
                }
            }
        }

        return ['id' => $pro->id, 'sku' => $pro->sku];
    }

    protected function getVariation($byID = false)
    {
        if ($this->var === null && $this->isCombination() || (isset($this->data['byID']) && $this->data['byID'] !== $byID)) {
            $variation = [];

            $this->buildProducts();
            foreach ($this->variants as $k => $val) {
                if (empty($val)) {
                    continue;
                }
                /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
                $newVariation = [
                    'id' => [$this->id],
                    'sku' => [$this->sku],
                    'acquisition_price' => 0,
                    'price' => $this->getPricesVarNow('price'),
                    'sale_price' => $this->getPricesVarNow('sale_price'),
                    'availability' => $this->availability,
                    'stock' => 0,
                    'size' => null,
                    'color' => null,
                ];
                foreach ($val as $val0) {
                    $id = $val0['option_id'];
                    $name = $val0['variant_name'];

                    if ($this->isColor($val0['option_id'])) {
                        $newVariation['color'] = $val0['variant_name'];
                    } elseif ($this->isSize($val0['option_id'])) {
                        $newVariation['size'] = $val0['variant_name'];
                    }

                    $newVariation['id'][] = $val0['option_id'];
                    $newVariation['id'][] = $val0['variant_id'];

                    $newVariation['sku'][] = $val0['variant_name'];

                    if ($val0['modifier'] != 0) {
                        if ($val0['modifier_type'] == 'A') {
                            $newVariation['price'] += $val0['modifier'];
                            $newVariation['sale_price'] += $val0['modifier'];
                        } else {
                            $newVariation['price'] += ($newVariation['price'] * ($val0['modifier'] / 100));
                            $newVariation['sale_price'] += ($newVariation['sale_price'] * ($val0['modifier'] / 100));
                        }
                    }

                    if ($newVariation['stock'] === 0) {
                        $newVariation['stock'] = $this->amount;
                    } else {
                        $newVariation['stock'] = min($newVariation['stock'], $this->amount);
                    }
                }
                $newVariation['sale_price'] = $this->getPricesAfterPromo($newVariation['sale_price']);

                $newVariation['id'] = str_replace(' ', '_', implode('_', $newVariation['id']));
                $newVariation['sku'] = str_replace(' ', '_', implode('_', $newVariation['sku']));

                $newVariation['price'] = $this->toDigit($newVariation['price']);
                $newVariation['sale_price'] = $this->toDigit($newVariation['sale_price']);

                if (empty($newVariation['size'])) {
                    unset($newVariation['size']);
                }

                if (empty($newVariation['color'])) {
                    unset($newVariation['color']);
                }

                $variation[$byID ? $newVariation['id'] : $k] = $newVariation;
            }
            $this->data['byID'] = $byID;
            $this->var = $variation;
        }

        if (!empty($this->var)) {
            return ['variation' => $this->var];
        }

        return null;
    }

    protected function getVariantData()
    {
        if ($this->varData === null && $this->isCombination()) {
            $variation = [];
            $this->getDefault = [];
            $this->buildProducts();
            foreach ($this->variants as $k => $val) {
                if (empty($val)) {
                    continue;
                }
                $newVariation = ['id' => [$this->id], 'sku' => [$this->sku]];

                foreach ($val as $val0) {
                    $newVariation['id'][] = $val0['option_id'];
                    $newVariation['id'][] = $val0['variant_id'];
                    $newVariation['sku'][] = $val0['variant_name'];
                }

                $newVariation['id'] = str_replace(' ', '_', implode('_', $newVariation['id']));
                $newVariation['sku'] = str_replace(' ', '_', implode('_', $newVariation['sku']));

                $variation[$newVariation['id']] = $newVariation;
            }
            $this->varData = $variation;
        }

        if (!empty($this->varData)) {
            return $this->varData;
        }

        return null;
    }

    /*
        public static function getVariant($id) {
            $variants = self::getVariation();
            $found = array_search($id, Config::searchIn($variants, 'id'));
            return isset($variants[$found]) ? $variants[$found] : null;
        }
        protected function getVariantData($id = null)
        {
            if (!array_key_exists($id, $this->variant)) {
                $combinations = [
                    'id' => $this->id,
                    'sku' => $this->sku,
                ];

                if ($this->isCombination()) {
                    // $this->getVariation();
                    foreach ($combination as $val) {
                        $combinations['id'] .= '_' . $val['id_attribute'];
                        $combinations['sku'] .= '_' . $val['attribute_name'];
                    }
                }

                $this->variant[$id] = $combinations;
            }

            return $this->variant[$id];
        }

    */

    protected function toFeed()
    {
        return $this->toArray(['variations', 'media_gallery']);
    }
}
