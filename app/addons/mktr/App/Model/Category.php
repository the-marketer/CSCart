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

class Category extends DataBase
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'hierarchy' => null,
        'url' => null,
        'image_url' => null,
    ];
    protected $ref = [
        'id' => 'category_id',
        'name' => 'category',
        'url' => 'getUrl',
        'hierarchy' => 'getParent',
        'image_url' => 'getImage',
    ];

    protected $functions = [
        'getUrl',
        'getParent',
        'getParentFeed',
        'getImage',
    ];

    protected $vars = [];
    protected $cast = [];

    protected $orderBy = 'category_id';
    protected $direction = 'ASC';
    protected $dateFormat = 'Y-m-d H:i';

    private static $i = null;
    private static $curent = null;
    private static $d = [];
    private static $ll = [];

    private static $shop = null;

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

    public static function getPage($num = 1, $limit = null)
    {
        $i = self::i();

        if ($limit === null) {
            $limit = $i->limit;
        }

        if ($num === null) {
            $num = 1;
        }

        $list = fn_get_categories([
            'page' => $num,
            'plain' => true,
            'items_per_page' => $limit,
            'simple' => true,
            'get_images' => true,
        ]);

        if ($list[1]['page'] == $num) {
            self::$ll = $list[0];

            return self::$ll;
        }

        return [];
    }

    public static function getByID($id, $data = null, $new = false)
    {
        $id = (int) $id;
        if ($new || !array_key_exists($id, self::$d)) {
            self::$d[$id] = new static();
            if ($data == null) {
                self::$d[$id]->data = fn_get_category_data($id);
            } else {
                self::$d[$id]->data = $data;
            }
        }
        self::$curent = self::$d[$id];

        return self::$curent;
    }

    protected function getUrl()
    {
        return fn_url('categories.view?category_id=' . $this->id);
    }

    protected function getParent()
    {
        $p = [$this->name];
        if ($this->parent_id != 0) {
            $ct = $this;
            while ($ct->parent_id != 0) {
                $ct = self::getByID($ct->parent_id);
                $p[] = $ct->name;
                if ($ct->parent_id != 0) {
                    $ct = self::getByID($ct->parent_id);
                }
            }
        }
        krsort($p);

        return implode('|', $p);
    }

    protected function getImage()
    {
        return isset($this->main_pair['detailed']['image_path']) ? $this->main_pair['detailed']['image_path'] : null;
    }
}
