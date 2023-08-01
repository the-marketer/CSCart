<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/
if (!defined('BOOTSTRAP')) {
    exit('Access denied');
}

if ($mode === 'api') {
    Mktr::i();

    return \Mktr\Helper\Route::initContent($action);
}

if ($mode === 'cron') {
    Mktr::i();
    \Mktr\Route\Cron::run();

    return [CONTROLLER_STATUS_OK];
}
