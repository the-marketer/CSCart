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

Mktr::i();
if (Mktr::$loadJSData) {
$events = [];

if (\Mktr\Model\Config::showJs(true)) {
    $c = 'window.mktr = window.mktr || {};

window.mktr.debug = function () { if (typeof dataLayer != "undefined") { for (let i of dataLayer) { console.log("Mktr", "Google", i); } } };
window.mktr.ready = false;
window.mktr.pending = [];
window.mktr.retryCount = 0;
window.mktr.loading = true;

';

    $conf = \Mktr\Model\Config::i();
    if (\Mktr\Model\Config::showGoogle()) {
        $c = $c . "(function(w,d,s,l,i){
w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl; f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','" . $conf->google_tagCode . "');

";
    }
    $rewrite = \Mktr\Model\Config::getSeo();
    // $rewrite = false;

    $c = $c . '(function(d, s, i) {
var f = d.getElementsByTagName(s)[0], j = d.createElement(s);j.async = true;
j.src = "https://t.themarketer.com/t/j/" + i; f.parentNode.insertBefore(j, f);
window.mktr.ready = true;
})(document, "script", "' . $conf->tracking_key . '");

window.mktr.eventsName = {
"home_page":"__sm__view_homepage",
"category":"__sm__view_category",
"brand":"__sm__view_brand",
"product":"__sm__view_product",
"add_to_cart":"__sm__add_to_cart",
"remove_from_cart":"__sm__remove_from_cart",
"add_to_wish_list":"__sm__add_to_wishlist",
"remove_from_wishlist":"__sm__remove_from_wishlist",
"checkout":"__sm__initiate_checkout",
"save_order":"__sm__order",
"search":"__sm__search",
"set_email":"__sm__set_email",
"set_phone":"__sm__set_phone"
};

window.mktr.buildEvent = function (name = null, data = {}) {
if (data === null) { data = {}; }
if (name !== null && window.mktr.eventsName.hasOwnProperty(name)) { data.event = window.mktr.eventsName[name]; }
' . (MKTR_DEV ? 'if (!window.mktr.eventsName.hasOwnProperty(name)){ data.event = name; data.type = "notListed"; }' : '') . '
if (typeof dataLayer != "undefined" && data.event != "undefined" && window.mktr.ready) {
dataLayer.push(data);' . (MKTR_DEV ? ' window.mktr.debug();' : '') . '
} else { window.mktr.pending.push(data); setTimeout(window.mktr.retry, 1000); } }

window.mktr.retry = function () {
if (typeof dataLayer != "undefined" && window.mktr.ready) {
for (let data of window.mktr.pending) { if (data.event != "undefined") { dataLayer.push(data);' . (MKTR_DEV ? ' window.mktr.debug();' : '') . ' } }        
} else if (window.mktr.retryCount < 6) { window.mktr.retryCount++; setTimeout(window.mktr.retry, 1000); }
};

window.mktr.loadEvents = function () { let time = (new Date()).getTime(); window.mktr.loading = true;
jQuery.get(window.mktr.base + "' . ($rewrite ? 'mktr/api/GetEvents?smuid=' . \Mktr\Helper\Session::getUid() . '&' : '?dispatch=mktr.api.GetEvents&') . 'mktr_time="+time, {}, function( data ) {
for (let i of data) { window.mktr.buildEvent(i[0],i[1]); }
});
};

window.mktr.toCheck = function (data, d = null) {
if (data != null && typeof data.search == "function" && window.mktr.loading) {
' . (MKTR_DEV ? ' console.log("mktr_data", data, d);' : '') . '
if (data.search("cart") != -1 || data.search("cos") != -1 || data.search("wishlist") != -1 &&
    data.search("getAllWishlist") == -1 || d !== null && typeof d == "string" && d.search("cart") != -1) {
    window.mktr.loading = false;
    setTimeout(window.mktr.loadEvents, 1000);
} else if(data.search("subscription") != -1) {
    window.mktr.loading = false;
    setTimeout(function () {
        let time = (new Date()).getTime();
        let add = document.createElement("script"); add.async = true;
        add.src = window.mktr.base + "' . ($rewrite ? 'mktr/api/setEmail?smuid=' . \Mktr\Helper\Session::getUid() . '&' : '?dispatch=mktr.api.setEmail&') . 'mktr_time="+time;
        let s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(add,s);
    }, 1000);
} } };
(function (_, $) {
$.ceEvent("on", "ce.ajaxdone", function (elms, scripts, params, responseData, responseText) {
    // console.log("EAX", scripts, params);
    if (typeof params != "undefined" &&
        typeof params.data != "undefined" &&
        params.data.result_ids != null &&
        typeof params.data.result_ids != "undefined" &&
        typeof params.data.result_ids.search == "function" &&
        params.data.result_ids.search("cart_status") != -1
    ) { window.mktr.loading = false; setTimeout(window.mktr.loadEvents, 1000); }
});
})(Tygh, Tygh.$);
';

    $evList = [
        'set_email' => 'setEmail',
        'set_phone' => 'setEmail',
        'save_order' => 'saveOrder',
    ];
    $add = [
        'setEmail' => false,
        'saveOrder' => false,
    ];

    $data = null;
    $action = Mktr\Helper\Valid::getParamReq('dispatch');
    switch ($action) {
        case '':
        case 'index.index':
            $action = 'home_page';
            break;
        case 'categories.view':
            $action = 'category';
            $data = Mktr\Helper\Valid::toJson(['category' => Mktr\Model\Category::getByID(Mktr\Helper\Valid::getParamReq('category_id'))->hierarchy]);
            break;
        case 'product_features.view':
            $action = 'brand';
            $data = ['name' => Mktr\Model\Brand::getByID(Mktr\Helper\Valid::getParamReq('variant_id'))->name];
            break;
        case 'products.search':
            $action = 'search';
            $data = ['search_term' => Mktr\Helper\Valid::getParamReq('q')];
            break;
        case 'product.view':
        case 'products.view':
            $action = 'product';
            $data = ['product_id' => Mktr\Helper\Valid::getParamReq('product_id')];
            break;
        case 'checkout.checkout':
            // case 'cart':
            $data = 0;
            $action = 'checkout';
            $data = null;
            break;
        default:
    }

    if ($data === null) {
        $data = 'null';
    } elseif (is_array($data)) {
        $data = Mktr\Helper\Valid::toJson($data);
    }

    $id_order = Mktr\Helper\Valid::getParamReq('order_id');

    $events[] = '<script type="text/javascript"> window.mktr = window.mktr || {}; ';
    $events[] = 'window.mktr.base = "' . \fn_url('') . '"';
    $events[] = 'window.mktr.base = window.mktr.base.substr(window.mktr.base.length - 1) === "/" ? window.mktr.base : window.mktr.base+"/";';
    $events[] = 'window.mktr.run = function () {';
    if ($action !== null) {
        $events[] = 'window.mktr.buildEvent("' . $action . '", ' . ($data === null ? 'null' : $data) . ');';
    }
    if ($id_order !== null) {
        Mktr\Helper\Session::set('save_order', [$id_order]);
        $events[] = 'window.mktr.buildEvent("save_order", ' . Mktr\Model\Orders::getByID($id_order)->toEvent(true) . ');';
    }
    $sessionData = \Mktr\Helper\Session::data();
    foreach ($sessionData as $event => $value) {
        if (in_array($event, ['add_to_cart', 'remove_from_cart', 'add_to_wish_list', 'remove_from_wishlist']) && !empty($value)) {
            $events[] = ' window.mktr.loadEvents();';
            break;
        }
    }
    $events[] = '};';
    if (!empty($conf->selectors)) {
        $events[] = '$("' . $conf->selectors . '").on("click", window.mktr.loadEvents);';
    }
    $events[] = '(typeof window.mktr.buildEvent != "function") ? document.addEventListener("mktr_loaded", function () { window.mktr.run(); }) : window.mktr.run();';
    $events[] = ' </script>';
    foreach ($evList as $key => $value) {
        if (!empty(Mktr\Helper\Session::get($key)) && $add[$value] === false) {
            $add[$value] = true;
            $events[] = '<script type="text/javascript"> (function(){ let add = document.createElement("script"); add.async = true; add.src = window.mktr.base + "' . ($rewrite ? 'mktr/api/' . $value . '?smuid=' . \Mktr\Helper\Session::getUid() . '&' : '?dispatch=mktr.api.' . $value . '&') . 'mktr_time="+(new Date()).getTime(); let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })(); </script>';
            $events[] = '<noscript><iframe src="' . \fn_url('') . ($rewrite ? 'mktr/api/' . $value . '?smuid=' . \Mktr\Helper\Session::getUid() . '&' : '?dispatch=mktr.api.' . $value . '&') . 'mktr_time=' . time() . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
        }
    }
} else {
    $c = '';
}

\Tygh\Registry::get('view')->assign('mktr', $c);
\Tygh\Registry::get('view')->assign('mktr_events', PHP_EOL . implode(PHP_EOL, $events));
\Tygh\Registry::get('view')->assign('mktr_status', Mktr::$loadJSData);
    Mktr::$loadJSData = false;
}
