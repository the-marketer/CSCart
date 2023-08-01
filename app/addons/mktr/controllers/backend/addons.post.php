<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/
defined('BOOTSTRAP') or exit('Access denied');
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET']) && $mode === 'update' && $_REQUEST['addon'] === 'mktr') {
    Mktr::i();
    Mktr\Helper\Admin::postData();
    Mktr\Helper\Admin::Addons($mode);
}
