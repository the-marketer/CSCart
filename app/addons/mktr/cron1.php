<?php

$dir = __DIR__ . '/';

set_time_limit(36000);
ini_set('memory_limit', '10G');
ini_set('max_execution_time', 36000);
// die();

define('MKTR_ROOT', $dir);
define('MKTR_LIB', $dir);
// define('MKTR_ROOT', DIR_ROOT . (substr(DIR_ROOT, -1) === '/' ? '' : '/'));
define('MKTR_APP', __DIR__ . (substr(__DIR__, -1) === '/' ? '' : '/'));

if (is_file($dir . 'App/Helper/Array2XML.php')) {
    require_once $dir . 'App/Helper/FileSystem.php';
    require_once $dir . 'App/Helper/Array2XML.php';
    require_once $dir . 'App/Helper/Data.php';
    require_once $dir . 'App/Helper/DataStorage.php';
    require_once $dir . 'App/Helper/Valid.php';
    require_once $dir . 'App/Model/Config.php';
}

class Cron
{
    public $data = null;
    public $status = false;
    public $DataStorage = null;
    public $storeData = null;
    public $store = [];
    public $Array2XML = false;

    public function __construct()
    {
        $this->data = \Mktr\Helper\Data::init();
        $this->DataStorage = new \Mktr\Helper\DataStorage('DataStorage');
        $this->store = $this->data->store;
        $this->storeData = $this->DataStorage->storeData;
        if ($this->storeData === null) {
            $this->storeData = [];
        }
        if ($this->store === null) {
            $this->store = [];
        }
    }

    public function start()
    {
        foreach ($this->store as $k => $s) {
            $out = $this->run($s);
            if ($out[1]) {
                $this->status = true;
                $this->store[$k] = $out[0];
            }
        }

        if ($this->status) {
            $this->data = new \Mktr\Helper\Data();
            $this->data->store = $this->store;
            $this->data->save();
        }

        echo json_encode($this->data->store) . PHP_EOL;
    }

    public function run($store, $status = false)
    {
        $time = time();
        $saveLimit = $store['limit'];
        $store['limit'] = 200;
        if ($store['cron_feed'] == 1 && $store['update_feed_time'] < $time) {
            $run = true;

            if (!isset($this->storeData[$store['store_id']]) || isset($this->storeData[$store['store_id']]['restart']) && $this->storeData[$store['store_id']]['restart']) {
                $page = $store['page'];
                $this->storeData[$store['store_id']] = ['page' => $store['page'], 'data' => []];
            } else {
                $page = $this->storeData[$store['store_id']]['page']++;
            }

            while ($run) {
                $url = $store['link'] . '?dispatch=mktr.api.feed&key=' . $store['rest_key'] . '&page=' . $page . '&limit=' . $store['limit'] . '&mime-type=json&no_save=1&t=' . time();
                $content = @file_get_contents($url);

                if (empty($content)) {
                    $this->getOnePage($page, $store);
                    $this->storeData[$store['store_id']]['page'] = $page;
                // $this->DataStorage->storeData = $this->storeData;
                // $this->DataStorage->save();
                } else {
                    $xmlArray = json_decode($content, true);
                    if (isset($xmlArray['products']['product'])) {
                        $this->storeData[$store['store_id']]['page'] = $page;
                        foreach ($xmlArray['products']['product'] as $k => $p) {
                            $this->storeData[$store['store_id']]['data'][] = $p;
                        }
                        $this->DataStorage->storeData = $this->storeData;
                        $this->DataStorage->save();
                        if (empty($xmlArray['products']['product'])) {
                            $run = false;
                        }
                    } else {
                        $run = false;
                    }
                }
                ++$page;
                // sleep(1);
            }

            $this->storeData[$store['store_id']]['restart'] = true;
            $this->DataStorage->storeData = $this->storeData;
            $this->DataStorage->save();

            $this->Array2XML();

            \Mktr\Helper\FileSystem::writeFile('products.' . $store['store_id'] . '.xml', \Mktr\Helper\Array2XML::cXML('products',
                ['product' => $this->storeData[$store['store_id']]['data']]
            )->saveXML());

            $store['update_feed_time'] = strtotime('+' . $store['update_feed'] . ' hour');
            $store['stop_page'] = $page;
            $status = true;
        }

        if ($store['cron_review'] == 1 && $store['update_review_time'] < time()) {
            @file_get_contents($store['link'] . '?dispatch=mktr.api.Reviews&key=' . $store['rest_key'] . '&start_date=' . strtotime('-' . ($store['update_review'] + 1) . ' hour'));
            $store['update_review_time'] = strtotime('+' . $store['update_review'] . ' hour');
            $status = true;
        }
        $store['limit'] = $saveLimit;

        return [$store, $status];
    }

    public function getOnePage($page, $store)
    {
        $end = $page * $store['limit'];
        $start = $end - $store['limit'];

        while ($start <= $end) {
            $url = $store['link'] . '?dispatch=mktr.api.feed&key=' . $store['rest_key'] . '&page=' . $start . '&limit=1&no_save=1&mime-type=json&t=' . time();
            $content = @file_get_contents($url);

            if ($content !== false) {
                $xmlArray = json_decode($content, true);
            } else {
                $xmlArray = [];
            }

            if (isset($xmlArray['products']['product'])) {
                foreach ($xmlArray['products']['product'] as $k => $p) {
                    $this->storeData[$store['store_id']]['data'][] = $p;
                }
                if (empty($xmlArray['products']['product'])) {
                    $start = $end;
                }
            }
            ++$start;
            // sleep(1);
        }
    }

    public function Array2XML()
    {
        if (!$this->Array2XML) {
            \Mktr\Helper\Array2XML::setCDataValues(['name', 'description', 'category', 'brand', 'size', 'color', 'hierarchy']);
            \Mktr\Helper\Array2XML::$noNull = true;
            $this->Array2XML = true;
        }
    }
}

$cron = new Cron();
$cron->start();
