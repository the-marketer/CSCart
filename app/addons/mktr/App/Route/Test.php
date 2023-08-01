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

use Tygh\Registry;

class Test
{
    public static function run()
    {
        dd($_SERVER, Registry::get('config'));
        // $d = \Mktr\Model\Product::getByID(289)->toArray();
        $d = [];
        // dd($d);
        $d1 = \Mktr\Helper\Session::get('DataSave');

        // $d[1]['products'][$d[2]];
        // dd($d[1]['products'][$d[2]], $d);
        if (is_array($d1[1])) {
            foreach ($d1[1] as $k => $v) {
                if (strpos($k, 'feature_') !== false) {
                    $d = str_replace('feature_', '', $k) . '_' . $v;
                }
            }
        }
        dd($d, $d1);
        // 289_548_1195
        // $status = Config::db()->getField('SELECT status FROM ?:addons WHERE addon = ?s', 'seo') == 'A';
        dd(\Mktr\Model\Config::getSeo());

        // \Mktr\Helper\Session::set('Alex', 'test123456 Alex');
        // \Mktr\Helper\Session::save();
        // \Mktr\Helper\Session::clear();
        // \Mktr\Helper\Session::save();
        dd(\Mktr\Helper\Session::get('Alex'), \Mktr\Helper\Session::getUid());
        // \Mktr\Helper\Session::
        // \Mktr\Model\Subscription::getByEmail('admin@eax.ro');
        // \Mktr\Model\Orders::getByID(100)->toEvent(true);
        return \Mktr\Helper\Valid::toJson(\Mktr\Model\Orders::getByID(100)->toApi());
    }
}
