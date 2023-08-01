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

class Reviews
{
    private static $i = null;
    private static $repo = null;

    public static function i()
    {
        if (self::$i == null) {
            self::$i = new self();
        }

        return self::$i;
    }

    public static function repo()
    {
        if (self::$repo == null) {
            self::$repo = \Tygh\Addons\ProductReviews\ServiceProvider::getProductReviewRepository();
        }

        return self::$repo;
    }

    public static function addFromApi($value)
    {
        $customer = fn_get_users(['email' => $value->review_email, 'items_per_page' => 1], \Tygh\Tygh::$app['session']['auth']);

        $review = [
            'product_id' => $value->product_id,
            'rating_value' => round((int) $value->rating / 2),
            'comment' => $value->review_text,
            'product_review_timestamp' => (new \DateTime($value->review_date))->getTimestamp(),
            'is_buyer' => 'Y',
            'terms' => 'Y',
            'status' => 'A',
        ];

        if (!empty($customer[0])) {
            $customer = $customer[0][0];

            $review['user_id'] = $customer['user_id'];
            $review['name'] = implode(' ', [$customer['lastname'], $customer['firstname']]);
        } else {
            $review['user_id'] = '0';
            $review['name'] = $value->review_author;
        }

        return self::repo()->create($review);
    }
}
