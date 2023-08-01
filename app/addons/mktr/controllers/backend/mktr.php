<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

use Tygh\Tools\Url;

if (!defined('BOOTSTRAP')) {
    exit('Access denied');
}

if (in_array($mode, ['tracker', 'google'])) {
    return [
        CONTROLLER_STATUS_OK,
        Url::buildUrn(['addons', 'update'], [
            'addon' => 'mktr',
            'selected_sub_section' => $mode,
            'selected_section' => 'settings',
        ]),
    ];
}

if ($mode === 'reset') {
    Mktr::i();
    \Mktr\Model\Config::delete();
    \Mktr\Model\Config::updateOptIn();
    \Mktr\Model\Config::updatePushStatus();

    return [
        CONTROLLER_STATUS_OK,
        Url::buildUrn(['addons', 'update'], [
            'addon' => 'mktr',
            'selected_sub_section' => 'tracker',
            'selected_section' => 'settings',
            'storefront_id' => $_REQUEST['storefront_id'],
        ]),
    ];
}

if ($mode === 'default') {
    Mktr::i();
    \Mktr\Model\Config::AddDefault();
    \Mktr\Model\Config::updateOptIn();
    \Mktr\Model\Config::updatePushStatus();
}

if ($mode === 'cron') {
    Mktr::i();
    \Mktr\Route\Cron::run();

    return [CONTROLLER_STATUS_OK];
}

return [
    CONTROLLER_STATUS_OK,
    Url::buildUrn(['addons', 'update'], ['addon' => 'mktr', 'selected_sub_section' => 'tracker', 'selected_section' => 'settings']),
];
