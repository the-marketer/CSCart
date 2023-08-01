<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Route;

class LoadEvents
{
    public static function run()
    {
        $evList = [
            'add_to_cart' => '__sm__add_to_cart',
            'remove_from_cart' => '__sm__remove_from_cart',
            'add_to_wish_list' => '__sm__add_to_wishlist',
            'remove_from_wishlist' => '__sm__remove_from_wishlist',
        ];
        $events = [];

        // $data = \Mktr\Helper\Session::data();

        $toClean = [];
        foreach ($evList as $event => $value) {
            $list = \Mktr\Helper\Session::get($event);
            if (!empty($list)) {
                foreach ($list as $ey => $value1) {
                    $pId = $value1[0];
                    $pAttr = $value1[1];

                    $variant = \Mktr\Model\Product::getProductVariant($pId, $pAttr);

                    $add = [
                        'product_id' => $pId,
                        'variation' => $variant,
                    ];

                    if (in_array($event, ['add_to_cart', 'remove_from_cart'])) {
                        $add['qty'] = $value1[2];
                    }

                    $value1 = $add;

                    $events[] = "window.mktr.buildEvent('" . $event . "', " . \Mktr\Helper\Valid::toJson($value1) . ');';
                    $toClean[] = $ey;
                }
                $vv = \Mktr\Helper\Session::get($event);

                foreach ($toClean as $vd) {
                    unset($vv[$vd]);
                }

                \Mktr\Helper\Session::set($event, $vv);
            }
        }

        \Mktr\Helper\Session::save();

        return implode(PHP_EOL, $events);
    }
}
