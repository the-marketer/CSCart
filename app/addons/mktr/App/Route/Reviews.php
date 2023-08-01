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
use Mktr\Model\Config;

class Reviews
{
    public static function run()
    {
        $t = Valid::getParam('start_date', date('Y-m-d'));
        $xml = null;

        $o = \Mktr\Helper\Api::send('product_reviews', ['t' => strtotime($t)], false);
        if ($o === false) {
            return '';
        }

        $xml = simplexml_load_string($o->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $added = [];
        $data = \Mktr\Helper\Data::init();
        $revStore = $data->{'reviewStore' . Config::i()->rest_key};

        foreach ($xml->reviews as $value) {
            if (isset($value->review_date)) {
                if (!isset($revStore[(string) $value->review_id])) {
                    $rev = \Mktr\Model\Reviews::addFromApi($value);

                    if ($rev !== null) {
                        $added[(string) $value->review_id] = $rev;
                    }
                } else {
                    $added[(string) $value->review_id] = $data->reviewStore[(string) $value->review_id];
                }
            }
        }

        $data->{'reviewStore' . Config::i()->rest_key} = $added;
        $data->save();

        return $xml;
    }
}
