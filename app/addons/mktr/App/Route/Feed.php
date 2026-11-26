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
    /**
     * With &page=N: only that page. Without it: every page, until the first empty one.
     * Pages only contain feed-eligible products (see Product::feedEligibilitySql).
     */
    public static function run()
    {
        $page = Valid::getParam('page', null);
        $limit = Valid::getParam('limit', null);

        $data = [];

        if ($page !== null) {
            self::exportRows(Product::getPage($page, $limit), $data);

            return $data;
        }

        for ($currentPage = 1; $rows = Product::getPage($currentPage, $limit); ++$currentPage) {
            self::exportRows($rows, $data);
        }

        return $data;
    }

    private static function exportRows(array $rows, array &$data)
    {
        foreach ($rows as $row) {
            $prod = Product::getByID($row['product_id'], true);
            if ($prod->isFeedEligible()) {
                $data[] = $prod->toArray();
            }
        }
    }
}
