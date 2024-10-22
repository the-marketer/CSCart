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


class Admin
{
    private static $i = null;
    private static $page = 'mktr_tracker';
    private static $config = null;
    private static $product_features = null;
    private static $req = true;

    private static $err = [
        'log' => [],
        'msg' => [
            'rest_key' => 'No REST API Key provided.',
            'tracking_key' => 'No Tracking API Key provided.',
            'customer_id' => 'No Customer ID provided.',
        ],
    ];

    public function __construct()
    {
        self::$i = $this;
    }

    public static function i()
    {
        if (self::$i === null) {
            self::$i = new self();
        }

        return self::$i;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } else {
            if (MKTR_DEV) {
                throw new \Exception("Method {$name} does not exist.");
            }

            return null;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::i(), $name)) {
            return call_user_func_array([self::$i, $name], $arguments);
        } else {
            if (MKTR_DEV) {
                throw new \Exception("Static method {$name} does not exist.");
            }

            return null;
        }
    }

    private function install()
    {
        \Mktr\Model\Config::AddDefault();

        $data = \Mktr\Model\Config::nws();

        \Mktr\Model\Config::setConfig('email_status', [
            'CONFIRMATION' => \Mktr\Model\Config::getConfig($data['CONFIRMATION'][0], $data['CONFIRMATION'][1]),
            'NOTIFICATION' => \Mktr\Model\Config::getConfig($data['NOTIFICATION'][0], $data['NOTIFICATION'][1]),
        ]);

        setcookie('Install', 'Yes' . time());
    }

    private function uninstall()
    {
        $data = \Mktr\Model\Config::nws();
        $old = \Mktr\Model\Config::getConfig('email_status');
        if ($old !== false) {
            \Mktr\Model\Config::setConfig($data['CONFIRMATION'][0], $old['CONFIRMATION'], $data['CONFIRMATION'][1]);
            \Mktr\Model\Config::setConfig($data['NOTIFICATION'][0], $old['NOTIFICATION'], $data['NOTIFICATION'][1]);
        }

        setcookie('unInstall', 'Yes' . time());
    }

    private function rq()
    {
        if (self::$req) {
            
            self::$req = false;
            $_REQUEST['selected_sub_section'] = isset($_REQUEST['selected_sub_section']) && $_REQUEST['selected_sub_section'] === 'mktr_google' ? 'mktr_google' : 'mktr_tracker';
            $_REQUEST['selected_section'] = (empty($_REQUEST['selected_section']) || $_REQUEST['selected_section'] !== 'settings' ? 'settings' : $_REQUEST['selected_section']);
            self::$page = $_REQUEST['selected_sub_section'];
            self::$config = \Mktr\Model\Config::i();
        }
    }

    private function Addons($mode = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'update' && $_REQUEST['addon'] === 'mktr') {
            $this->rq();
            $tabs = [];
            if (self::$page === 'mktr_tracker') {
                $tabs = [
                    'settings' => [
                        'title' => 'Tracker Settings',
                        'js' => true,
                    ],
                    'mktr_google' => [
                        'href' => 'addons.update&addon=mktr&selected_sub_section=mktr_google&selected_section=settings',
                        'title' => 'Google Settings',
                    ],
                    'detailed' => [
                        'title' => 'General',
                        'js' => true,
                    ],
                ];
            } else {
                $tabs = [
                    'mktr_tracker' => [
                        'href' => 'addons.update&addon=mktr&selected_sub_section=mktr_tracker&selected_section=settings',
                        'title' => 'Tracker Settings',
                    ],
                    'settings' => [
                        'title' => 'Google Settings',
                        'js' => true,
                    ],
                    'detailed' => [
                        'title' => 'General',
                        'js' => true,
                    ],
                ];
            }
            $tabs = [
                'settings' => [
                    'title' => 'Main Settings',
                    'js' => true,
                ],
                'detailed' => [
                    'title' => 'General',
                    'js' => true,
                ],
            ];
            if (self::$config->shop() !== 0) {
                $tabs['reset'] = [
                    'title' => 'Reset to Main',
                    'href' => 'mktr.reset&storefront_id=' . self::$config->shop(),
                ];
            } else {
                $tabs['default'] = [
                    'title' => 'Reset to Default',
                    'href' => 'mktr.default'
                ];
            }
            \Tygh\Registry::set('navigation.tabs', $tabs);
        }
    }

    private function postData()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['addon'] === 'mktr') {
            if (isset($_POST['mktr_update'])) {
                $this->rq();
                $proccess = [];
                $form = self::FormData();
                /*foreach ($form as $key1 => $value1) {
                    foreach ($form[$key1] as $key => $value) {
                        $vv = $_POST['mktr_data'][$key];

                        if (in_array($key, ['rest_key', 'tracking_key', 'customer_id']) && empty($vv)) {
                            self::$err['log'][] = self::$err['msg'][$key];
                        }

                        if (in_array($key, ['cron_feed', 'cron_review', 'push_status', 'allow_export'])) {
                            $vv = (bool) $vv;
                        }

                        if (self::$config->{$key} != $vv) {
                            self::$config->{$key} = $vv;
                            $proccess[] = $key;
                        }
                    }
                    if (self::$config->tracking_key === '') {
                        self::$config->status = false;
                        $proccess[] = 'status';
                    }
                }*/
                foreach ($form[self::$page] as $key => $value) {
                    $vv = $_POST['mktr_data'][$key];

                    if (in_array($key, ['rest_key', 'tracking_key', 'customer_id']) && empty($vv)) {
                        self::$err['log'][] = self::$err['msg'][$key];
                    }

                    if (in_array($key, ['cron_feed', 'cron_review', 'push_status', 'allow_export'])) {
                        $vv = (bool) $vv;
                    }

                    if (self::$config->{$key} != $vv) {
                        self::$config->{$key} = $vv;
                        $proccess[] = $key;
                    }
                }

                if (self::$config->tracking_key === '') {
                    self::$config->status = false;
                    $proccess[] = 'status';
                }
                
                foreach ($proccess as $key) {
                    switch ($key) {
                        case 'opt_in':
                            self::$config->updateOptIn();
                            break;
                        case 'push_status':
                            self::$config->updatePushStatus();
                            break;
                    }
                }
                self::$config->save();
            }
        }
    }

    public static function FormData()
    {
        // dd(\Tygh\Tygh::$app['storefront']->storefront_id);
        // $id = \Mktr\Model\Config::shop();
        // $id = $id == 0 ? '' : ' --storefront_id='.$id;
        return [
            'mktr_tracker' => [
                'status' => ['type' => 'switch', 'label' => 'Status'],
                'tracking_key' => ['type' => 'text', 'label' => 'Tracking API Key *'],
                'rest_key' => ['type' => 'text', 'label' => 'REST API Key *'],
                'customer_id' => ['type' => 'text', 'label' => 'Customer ID *'],
                'cron_feed' => ['type' => 'switch', 'label' => 'Activate Cron Feed', 'desc' => implode('<br />', ['<b>If Enable, Please Add this to your server Cron Jobs</b>',
                    // '<br /><code>0 */1 * * * /usr/bin/php ' . MKTR_APP . 'cron.php > /dev/null 2>&1</code>'
                    '<code>0 */1 * * * /usr/bin/php ' . MKTR_ROOT . 'index.php --dispatch=mktr.cron > /dev/null 2>&1</code>',
                    'OR',
                    '<code>php ' . MKTR_ROOT . 'index.php --dispatch=mktr.cron</code>',
                ])],
                'update_feed' => ['type' => 'text', 'label' => 'Cron Update feed every (hours)'],
                'cron_review' => ['type' => 'switch', 'label' => 'Activate Cron Review'],
                'update_review' => ['type' => 'text', 'label' => 'Cron Update Review every (hours)'],
                'opt_in' => [
                    'type' => 'select',
                    'label' => 'Double opt-in setting',
                    'options' => [
                        ['value' => 0, 'label' => 'WebSite'],
                        ['value' => 1, 'label' => 'The Marketer'],
                    ],
                ],
                'push_status' => ['type' => 'switch', 'label' => 'Push Notification'],
                'default_stock' => [
                    'type' => 'select',
                    'label' => 'Default Stock<br />if negative Stock Value',
                    'options' => [
                        ['value' => 0, 'label' => 'Out of Stock'],
                        ['value' => 1, 'label' => 'In Stock'],
                        ['value' => 2, 'label' => 'In supplier stock'],
                    ],
                ],
                'allow_export' => ['type' => 'switch', 'label' => 'Allow orders export'],
                'selectors' => ['type' => 'text', 'label' => 'Trigger Selectors'],
                'brand' => ['type' => 'multi_select', 'label' => 'Brand Attribute', 'options' => self::product_features()],
                'color' => ['type' => 'multi_select', 'label' => 'Color Attribute', 'options' => self::product_features()],
                'size' => ['type' => 'multi_select', 'label' => 'Size Attribute', 'options' => self::product_features()],
            ],
            'mktr_google' => [
                'google_status' => ['type' => 'switch', 'label' => 'Status'],
                'google_tagCode' => ['type' => 'text', 'label' => 'Tag CODE *'],
            ],
        ];
    }

    public static function product_features($type = null)
    {
        if (self::$product_features === null) {
            if (defined('PRODUCT_VERSION') && PRODUCT_VERSION === '4.3.1') {
                self::$product_features = [['value' => 0, 'label' => 'Please Select']];
            } else {
                $list = \Mktr\Model\Config::db()->query('SELECT `feature_id`,`description` FROM `?:product_features_descriptions` WHERE lang_code ="' . CART_LANGUAGE . '"');
                
                if (method_exists($list, 'fetchAll')) {
                    $list = $list->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $list = $list->fetch_all(MYSQLI_ASSOC);
                }
                
                self::$product_features = [['value' => 0, 'label' => 'Please Select']];
                foreach ($list as $k => $v) {
                    self::$product_features[] = ['value' => $v['feature_id'], 'label' => $v['description'] . ' (feature)'];
                }
                $list = \Mktr\Model\Config::db()->query('SELECT `option_id`,`option_name`,`internal_option_name` FROM `?:product_options_descriptions` WHERE lang_code ="' . CART_LANGUAGE . '"');

                if (method_exists($list, 'fetchAll')) {
                    $list = $list->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $list = $list->fetch_all(MYSQLI_ASSOC);
                }
                foreach ($list as $k => $v) {
                    $label = (empty($v['internal_option_name']) ? $v['option_name'] : $v['internal_option_name']);
                    self::$product_features[] = ['value' => $v['option_id'], 'label' => $label . ' (option)'];
                }
            }
        }

        return self::$product_features;
    }

    protected function getConfigForm($cPage = 'mktr_tracker')
    {
        $new = [];
        $n = null;
        $form = self::FormData();

        foreach ($form[$cPage] as $key => $value) {
            $desc = '';
            $input = '';
            if (array_key_exists('desc', $value)) {
                $desc = '<div class="row1"><b>' . $value['desc'] . '</b></div>';
            }
            if ($value['type'] === 'switch') {
                $input = self::switch_button($key, self::$config->{$key});
            } elseif ($value['type'] === 'text') {
                $input = '<input type="text" id="mktr_' . $key . '" name="mktr_data[' . $key . ']" value="' . self::$config->{$key} . '">';
            } elseif ($value['type'] === 'select') {
                $v = self::$config->{$key};
                $input = '<select id="mktr_' . $key . '" name="mktr_data[' . $key . ']">';

                foreach ($value['options'] as $value1) {
                    $input .= '<option value="' . $value1['value'] . '" ' . ($v === $value1['value'] ? 'selected' : '') . '>' . $value1['label'] . '</option>';
                }
                $input .= '</select>';
            } elseif ($value['type'] === 'multi_select') {
                $v = self::$config->{$key};
                $input = '<select id="mktr_' . $key . '" name="mktr_data[' . $key . '][]" multiple>';

                foreach ($value['options'] as $value1) {
                    $input .= '<option value="' . $value1['value'] . '" ' . (in_array($value1['value'], $v) ? 'selected' : '') . '>' . $value1['label'] . '</option>';
                }

                $input .= '</select>';
            }

            $new[] = '<div class="row1">
            <div class="col-25">
              <label for="mktr_' . $key . '"><b>' . $value['label'] . '</b></label>
            </div>
            <div class="col-75">
            ' . $input . '
            </div>
          </div>' . $desc;
        }

        return implode(PHP_EOL, $new);
    }

    private function switch_button($name = 'status', $value = false)
    {
        return '<input type="hidden" id="eswitch_' . $name . '_value" name="mktr_data[' . $name . ']" value="' . ($value ? '1' : '0') . '">
<input id="eswitch_' . $name . '" class="eswitch eswitch-flip" type="checkbox" onchange="document.querySelector(\'#eswitch_' . $name . '_value\').value=this.checked ? 1 : 0" ' . ($value ? 'checked' : '') . '/>
<label class="eswitch-btn" for="eswitch_' . $name . '" ></label>';
    }

    private function form($p = null)
    {
        $this->rq();
        $add = false;

        if ($p !== null) {
            if ($_REQUEST['selected_sub_section'] === $p) {
                $add = true;
            }
            $cPage = $p;
        }

        $out = '<style>#mktr .eswitch,#mktr .eswitch *,#mktr .eswitch :after,#mktr .eswitch :before,#mktr .eswitch:after,#mktr .eswitch:before{box-sizing:border-box}#mktr a,#mktr a[type=button]{cursor:pointer;float:right}#mktr{margin:0 5%}#mktr .eswitch{display:none!important}#mktr .eswitch:after::selection{background:0 0}#mktr .eswitch:before::selection{background:0 0}#mktr .eswitch :after::selection{background:0 0}#mktr .eswitch :before::selection{background:0 0}#mktr .eswitch ::selection{background:0 0}#mktr .eswitch+.eswitch-btn{box-sizing:border-box;outline:0;display:block;width:7em;height:32px;position:relative;cursor:pointer;user-select:none}#mktr .row1::after,#mktr a,#mktr select{width:100%!important}#mktr .eswitch+.eswitch-btn::selection{background:0 0}#mktr .eswitch+.eswitch-btn:after{position:relative;display:block;content:"";width:50%;height:100%;left:0}#mktr .eswitch+.eswitch-btn:before{position:relative;content:"";width:50%;height:100%;display:none}#mktr .eswitch-flip+.eswitch-btn:after,#mktr .eswitch-flip+.eswitch-btn:before{transition:.4s;width:100%;text-align:center;line-height:32px;font-weight:700;position:absolute;top:0;backface-visibility:hidden;border-radius:4px;color:#fff}#mktr .eswitch::selection{background:0 0}#mktr .eswitch:checked+.eswitch-btn:after{left:50%}#mktr .eswitch-flip+.eswitch-btn{padding:2px;transition:.2s;font-family:sans-serif;perspective:100px}#mktr .eswitch-flip+.eswitch-btn:after{display:inline-block;left:0;content:"ON";background:#02c66f;transform:rotateY(-180deg)}#mktr .eswitch-flip+.eswitch-btn:before{display:inline-block;left:0;background:#ff3a19;content:"OFF"}#mktr .eswitch-flip+.eswitch-btn:active:before{transform:rotateY(-20deg)}#mktr .eswitch-flip:checked+.eswitch-btn:before{transform:rotateY(180deg)}#mktr .eswitch-flip:checked+.eswitch-btn:after{transform:rotateY(0);left:0;background:#7fc6a6}#mktr .eswitch-flip:checked+.eswitch-btn:active:after{transform:rotateY(20deg)}#mktr select{padding:0 12px;border:1px solid #ccc;border-radius:4px;resize:vertical;max-width:100%!important}#mktr input[type=text],#mktr textarea{width:100%!important;padding:12px;border:1px solid #ccc;border-radius:4px;resize:vertical;max-width:100%!important}#mktr label{padding:12px 12px 12px 0;display:inline-block}#mktr a[type=button]{background-color:#0489cc;color:#fff;padding:12px 20px;border:none;border-radius:4px}#mktr a[type=button]:hover{background-color:#f4f3f3;color:#000}#mktr .col-25{float:left;width:25%;margin-top:6px}#mktr .col-75{float:left;width:75%;margin-top:10px}#mktr .row1::after{content:"";display:table;clear:both}@media screen and (max-width:600px){#mktr .col-25,#mktr .col-75,#mktr a,#mktr a[type=button]{width:100%!important;margin-top:0}}</style>
    <div id="mktr">
        <input type="hidden" name="mktr_update" value="' . $cPage . '">';
        $out .= $this->getConfigForm($cPage);
        $out .= '<br>
    <div class="row1">
        <a data-ca-dispatch="dispatch[addons.update]" data-ca-target-form="update_addon_mktr_form" class="btn cm-submit cm-addons-save-settings" >Save</a>
    </div>
    </div>
    ';
        if ($add) {

            $out .= "<style>/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  src: url(https://fonts.gstatic.com/s/plusjakartasans/v8/LDIbaomQNQcsA88c7O9yZ4KMCoOg4IA6-91aHEjcWuA_qU79QB_VMq2oRsWk0Fs.woff2) format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  src: url(https://fonts.gstatic.com/s/plusjakartasans/v8/LDIbaomQNQcsA88c7O9yZ4KMCoOg4IA6-91aHEjcWuA_qU79Qh_VMq2oRsWk0Fs.woff2) format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  src: url(https://fonts.gstatic.com/s/plusjakartasans/v8/LDIbaomQNQcsA88c7O9yZ4KMCoOg4IA6-91aHEjcWuA_qU79Qx_VMq2oRsWk0Fs.woff2) format('woff2');
  unicode-range: U+0100-02AF, U+0304, U+0308, U+0329, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  src: url(https://fonts.gstatic.com/s/plusjakartasans/v8/LDIbaomQNQcsA88c7O9yZ4KMCoOg4IA6-91aHEjcWuA_qU79TR_VMq2oRsWk.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* The Modal (background) */
.mktr-modal {
    display: none;
    position: fixed;
    z-index: 99999999999999;
    padding-top: 100px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.mktr-modal-body {
    position: relative;
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border: 1px solid #888;
    width: 60%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
    animation-duration: 0.4s;
}

.mktr-head {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 16px 36px;
    width: 100%;
    height: 64px;
    background: #FCFBFE;
    border-bottom: 1px solid #EAECF0;
}

.mktr-content {
    width: 70%;
    padding: 32px 0;
    text-align: left;
    font-family: Plus Jakarta Sans;
    font-size: 14px;
    font-weight: 400;
    line-height: 1;
}

.mktr-content-footer {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    margin: 26px 0;
}

.mktr-button {
    font-weight:bold;
    padding: 10px 24px;
    background-color: #2259FA;
    color: #FFFFFF;
    border-radius: 8px;
    border-width: 0;
}

.red {
    background-color: #fc0015;
    color: #FFFFFF;
}

a.mktr-button {
    color: #FFFFFF;
}

.mktr-button:hover {
    text-decoration: none;
    color: #FFFFFF;
}

.mktr-content
{
    width: 70% !important;
    padding: 32px 0px 32px 0px !important;
    justify-content: center;
    align-items: center;
    margin:auto;
    font-family: Plus Jakarta Sans !important;
    font-size: 14px !important;
    font-weight: 400;
    line-height: 1 !important;
    letter-spacing: 0em;
    text-align: left;
}
.mktr-content a
{
    text-decoration: none;
    color: #1B47C8;
}

.mktr-modal-body .mktr-content {
    padding: 0px 0px 0px 0px;
}

.mktr-content-head {
    border-bottom: 1px solid #EAECF0;
    width: 100% !important;
    max-width: 100% !important;
    padding-bottom: 10px;
}

.mktr-content-body {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 12px;
    border: 1px dotted;
    border-color: #EAECF0;
    margin-top: 12px;
}

.mktr-content-text {
    margin: 24px;
}

.mktr-content-footer {
    width: 100% !important;
    max-width: 100% !important;
    margin: 26px 0px !important;
    display: flex;
    flex: 2;
    flex-wrap: wrap;
}

.mktr-content-footer>*:first-child {
    flex-direction: flex-start !important;
}
.mktr-content-footer .mktr-content-right {
    margin-left: auto;
}
.mktr-content-footer .mktr-button {
    margin-top: -10px !important;
}
@media only screen and (max-width: 960px) {
    .mktr-modal-body {
        width: 80%;
    }
    .mktr-content {
        width: 90%;
    }
}

@media screen and (max-width: 782px) {
    .mktr-modal-body {
        width: 90%;
    }
}
@media screen and (max-width: 500px) {
    .mktr-content-footer .mktr-content-right {
        width: 100%;
    }
    .mktr-content-footer .mktr-button { 
        margin-top: 10px !important;
    }
}
</style>
<div class=\"mktr-modal\">
    <div class=\"mktr-modal-body\">
        <div class=\"mktr-head\"><img src=\"https://connector.themarketer.com/logo.png\"></div>
        <div class=\"mktr-content\">
            <div class=\"mktr-content-body\">
                <div class=\"mktr-content-text\">
                    <h4></h4>
                </div>
            </div>
            <div class=\"mktr-content-footer\">
                <div class=\"mktr-content-left\"><button type=\"button\" class=\"mktr-button no\">No!</button></div>
                <div class=\"mktr-content-right\"><button type=\"button\" class=\"mktr-button red yes\">Yes!</button></div>
            </div>
        </div>
    </div>
</div>";
$name = 'Store';

if (self::$config->shop() !== 0) {
    $name = \fn_get_storefront(self::$config->shop())->name;
}
            $out .= '<script type="text/javascript">
document.addEventListener("click", function(event){
    let show = { status: false, text: null };
    if (event.target.matches("#default [href*=\'mktr.default\']")) {
        show.status = true;
        show.text = "Are you sure you want to reset all Main settings to their default values?<br />This action cannot be undone.";
    } else if (event.target.matches("#reset [href*=\'mktr.reset\']")) {
        show.status = true;
        show.text = "Are you sure you want to reset \"'.$name.'\" settings to their Main settings values?<br />This action cannot be undone.";
    } else if (event.target.matches(".mktr-modal .mktr-button.yes")) {
        document.querySelector(".mktr-modal-body").style.display = "none";
        window.open(document.querySelector("#default [href*=\'mktr.default\'],#reset [href*=\'mktr.reset\']").href, "_self");
    } else if (event.target.matches(".mktr-modal .mktr-button.no")) { 
        document.querySelector(".mktr-modal").style.display = "none";
    }
    if (show.status) {
        event.preventDefault();
        document.querySelector(".mktr-modal .mktr-content-text h4").innerHTML = show.text;
        document.querySelector(".mktr-modal").style.display = "block";
    }
});
setTimeout(function() { let q = document.querySelector(".cm-js#mktr_' . $cPage . ',.cm-js#' . $cPage . '"); if (typeof q != "undefined") { q.click(); } }, 1000);
</script>';
        }

        return $out;
    }
}
