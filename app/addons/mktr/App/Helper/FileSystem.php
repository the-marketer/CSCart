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

class FileSystem
{
    private static $path = null;
    private static $lastPath = null;
    private static $status = [];
    private static $useRoot = false;

    private static $init = null;

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }

        return self::$init;
    }

    /** @noinspection PhpUnused */
    public static function setWorkDirectory($name = 'Storage')
    {
        if ($name != 'base' && !self::$useRoot) {
            self::$path = MKTR_APP . $name . '/';
        } else {
            self::$path = MKTR_ROOT;
        }

        return self::init();
    }

    public static function writeFile($fName, $content, $mode = 'w+')
    {
        self::$lastPath = self::getPath() . $fName;

        $file = fopen(self::$lastPath, $mode);
        fwrite($file, $content);
        fclose($file);

        self::$status[] = [
            'path' => self::getPath(),
            'fileName' => $fName,
            'fullPath' => self::getPath() . $fName,
            'status' => true,
        ];

        return self::init();
    }

    public static function rFile($fName, $mode = 'rb')
    {
        self::$lastPath = self::getPath() . $fName;

        if (self::fileExists($fName)) {
            $file = fopen(self::$lastPath, $mode);

            $contents = fread($file, filesize(self::$lastPath));

            fclose($file);
        } else {
            $contents = '';
        }

        return $contents;
    }

    public static function readFile($fName, $mode = 'rb')
    {
        $contents = '';
        self::$lastPath = self::getPath() . $fName;

        if (self::fileExists($fName)) {
            $file = fopen(self::$lastPath, $mode);

            $contents = fread($file, filesize(self::$lastPath));

            fclose($file);
        }

        return $contents;
    }

    /** @noinspection PhpUnused */
    public static function fileExists($fName)
    {
        return file_exists(self::getPath() . $fName);
    }

    /** @noinspection PhpUnused */
    public static function deleteFile($fName)
    {
        self::$lastPath = self::getPath() . $fName;

        if (self::fileExists($fName)) {
            unlink(self::$lastPath);
        }

        return true;
    }

    public static function getPath()
    {
        if (self::$path == null) {
            self::setWorkDirectory();
        }

        return self::$path;
    }

    /** @noinspection PhpUnused */
    public static function getLastPath()
    {
        return self::$lastPath;
    }

    public static function getStatus()
    {
        return self::$status;
    }

    public static function write($f, $c, $root = false)
    {
        $file = fopen(($root ? MKTR_ROOT : MKTR_APP) . $f, 'w+');
        fwrite($file, '/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

' . $c);
        fclose($file);
    }
}
