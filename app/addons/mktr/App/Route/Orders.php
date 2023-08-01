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

class Orders
{
    public static function run()
    {
        $data = [];
        $stop = false;
        $page = Valid::getParam('page', null);
        $limit = Valid::getParam('limit', null);
        $start_date = Valid::getParam('start_date', null);
        $end_date = Valid::getParam('end_date', null);

        if ($page !== null) {
            $stop = true;
        }

        $currentPage = $page === null ? 1 : $page;

        do {
            $cPage = \Mktr\Model\Orders::getPageByDate($currentPage, $start_date, $end_date, $limit);
            $pages = $stop ? 0 : count($cPage);
            foreach ($cPage as $val) {
                $order = \Mktr\Model\Orders::getByID($val['order_id'])->toArrayFeed();
                if ($order !== null) {
                    $data[] = $order;
                }
            }

            ++$currentPage;
        } while (0 < $pages);

        return $data;
    }
}
