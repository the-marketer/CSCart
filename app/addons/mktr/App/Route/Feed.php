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

use Mktr\Helper\Valid;
use Mktr\Model\Product;

class Feed
{
    public static function run()
    {
        $data = [];
        $stop = false;
        $page = Valid::getParam('page', null);
        $limit = Valid::getParam('limit', null);

        if ($page !== null) {
            $stop = true;
        }

        $currentPage = $page === null ? 1 : $page;

        do {
            $cPage = Product::getPage($currentPage, $limit);
            $pages = $stop ? 0 : count($cPage);

            foreach ($cPage as $val) {
                $data[] = Product::getByID($val['product_id'], true)->toArray();
            }

            ++$currentPage;
        } while (0 < $pages);

        return $data;
    }
}
