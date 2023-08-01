			<div class="control-group">
				<label class="control-label" for="elm_page_date">{__("tm_feed_date_from")}:</label>
				<div class="controls">
					{include file="common/calendar.tpl" date_id="elm_s_form_date" date_name="date_from" date_val=$subscription_form.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="orders_feeds">{__("tm_orders_feed")}:</label>
				<div class="controls">
					<span class="checkbox">
						<input class="input-large" type="text" name="orders_feeds" id="orders_feeds" value="{$config.current_location}/index.php?dispatch=themarketer.orders_export&key={$addons.themarketer.tm_rest_key}&customerId={$addons.themarketer.tm_customer_id}&start_date=2022-04-12&page=1" style="width:90%" />
					</span>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="orders_feeds">{__("tm_products_feed")}:</label>
				<div class="controls">
					<span class="checkbox">
						<input class="input-large" type="text" name="products_feeds" id="products_feeds" value="{$config.current_location}/var/files/themarketer/products_feed_{$addons.themarketer.tm_rest_key}.xml" style="width:90%" />
					</span>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="orders_feeds">{__("tm_products_feed_cron")}:</label>
				<div class="controls">
					<span class="checkbox">
						<input class="input-large" type="text" name="products_feeds_cron" id="products_feeds_cron" value="{$config.current_location}/index.php?dispatch=themarketer.products_feed&key={$addons.themarketer.tm_rest_key}" style="width:90%" />
					</span>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="orders_feeds">{__("tm_categories_feed")}:</label>
				<div class="controls">
					<span class="checkbox">
						<input class="input-large" type="text" name="categories_feeds" id="categories_feeds" value="{$config.current_location}/index.php?dispatch=themarketer.categories_feed&key={$addons.themarketer.tm_rest_key}" style="width:90%" />
					</span>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="orders_feeds">{__("tm_brands_feed")}:</label>
				<div class="controls">
					<span class="checkbox">
						<input class="input-large" type="text" name="brands_feeds" id="brands_feeds" value="{$config.current_location}/index.php?dispatch=themarketer.brands_feed&key={$addons.themarketer.tm_rest_key}" style="width:90%" />
					</span>
				</div>
			</div>
{literal}			
<script>
function replaceQueryParam(param, newval, search) {
    var regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
    var query = search.replace(regex, "$1").replace(/&$/, '');

    return (query.length > 2 ? query + "&" : "?") + (newval ? param + "=" + newval : '');
}

 var $dateInput = $('#elm_s_form_date');

$dateInput.datepicker({
    onSelect: function(f,d,i){
            $dateInput.trigger("change");
    }
}).data('datepicker');

$dateInput.on("change", function () {
	 var thisDate = $(this).val();
	 thisDate = thisDate.split('/'); 
	 var newdate = thisDate[2]+'-'+thisDate[0]+'-'+thisDate[1];
	 var linkstr = $('#orders_feeds').val(); 
	 linkstr = replaceQueryParam('start_date', newdate, linkstr);
	$('#orders_feeds').val(linkstr);
});

</script>
{/literal}		