<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Helper;

class Data
{
    private static $init = null;

    private static $data;

    public function __construct()
    {
        FileSystem::setWorkDirectory();

        $data = FileSystem::rFile('data.' . \Mktr\Model\Config::shop() . '.json');
        if ($data !== '') {
            self::$data = json_decode($data, true);
        } else {
            self::$data = [];
        }
    }

    public static function init($new = false)
    {
        if (self::$init == null || $new) {
            self::$init = new self();
        }

        return self::$init;
    }

    public function __get($name)
    {
        if (!isset(self::$data[$name])) {
            if ($name == 'update_feed' || $name == 'update_review') {
                self::$data[$name] = 0;
            } else {
                self::$data[$name] = null;
            }
        }

        return self::$data[$name];
    }

    public function __set($name, $value)
    {
        self::$data[$name] = $value;
    }

    public static function getData()
    {
        return self::$data;
    }

    public static function addTo($name, $value, $key = null)
    {
        if ($key === null) {
            self::$data[$name][] = $value;
        } else {
            self::$data[$name][$key] = $value;
        }
    }

    public static function del($name)
    {
        unset(self::$data[$name]);
    }

    public static function save()
    {
        FileSystem::writeFile('data.' . \Mktr\Model\Config::shop() . '.json', Valid::toJson(self::$data));
    }

    public static function writeFile($fName, $content, $mode = 'w+')
    {
        $file = fopen(MKTR_APP . 'Storage/' . $fName, $mode);
        fwrite($file, $content);
        fclose($file);
    }
}
