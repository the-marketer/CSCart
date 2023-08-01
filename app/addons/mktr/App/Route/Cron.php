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

class Cron
{
    public static function run()
    {
        $out = [];
        $pro = \Mktr\Model\Config::db()->query('SELECT * FROM ?:storefronts');
        foreach ($pro->fetch_all(MYSQLI_ASSOC) as $store) {
            foreach ($store as $k => $v) {
                \Tygh\Tygh::$app['storefront']->{$k} = $v;
            }

            foreach (['http_host', 'https_host', 'current_host'] as $v) {
                \Tygh\Registry::set('runtime.' . $v, $store['url']);
            }
            foreach (['http_location', 'origin_http_location'] as $v) {
                \Tygh\Registry::set('runtime.' . $v, 'http://' . $store['url']);
            }
            foreach (['https_location', 'origin_https_location', 'current_location'] as $v) {
                \Tygh\Registry::set('runtime.' . $v, 'https://' . $store['url']);
            }
            $_REQUEST['storefront_id'] = $store['storefront_id'];
            \Tygh\Registry::set('runtime.storefront_id', $store['storefront_id']);
            // \Mktr\Model\Config::setShop($store['storefront_id']);
            $c = \Mktr\Model\Config::i(true);

            if (\Mktr\Model\Config::showJS()) {
                $data = \Mktr\Helper\Data::init(true);

                $upFeed = $data->update_feed;
                $upReview = $data->update_review;

                if ($c->cron_feed && $upFeed < time()) {
                    $out[] = ['Run', $store['storefront_id'], \Mktr\Model\Config::shop()];
                    \Mktr\Model\Product::clear();

                    $d = Feed::run();

                    \Mktr\Helper\Array2XML::setCDataValues(['name', 'description', 'category', 'brand', 'size', 'color', 'hierarchy']);
                    \Mktr\Helper\Array2XML::$noNull = true;

                    $XML = \Mktr\Helper\Array2XML::cXML('products', ['product' => $d])->saveXML();

                    \Mktr\Helper\Data::writeFile('feed.' . \Mktr\Model\Config::shop() . '.xml', $XML);

                    $add = $c->update_feed;

                    $data->update_feed = strtotime('+' . (empty($add) ? 4 : $add) . ' hour');
                }
                /*
                else {
                    $out[] = [ 'NoRun', $store['storefront_id'], \Mktr\Model\Config::shop(), $_REQUEST['storefront_id'] ];
                }*/

                if ($c->cron_review && $upReview < time()) {
                    \Mktr\Route\Reviews::execute();

                    $data->update_review = strtotime('+' . $c->update_review . ' hour');
                }
                $data->save();
            }
        }
        // var_dump($out);
    }
}
