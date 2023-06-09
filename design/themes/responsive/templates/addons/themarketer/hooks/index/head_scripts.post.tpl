
{if $addons.themarketer.tm_track_key && $addons.themarketer.tm_customer_id && $addons.themarketer.enable_notifications == 'Y'}
  {literal}  
    <!-- Themarketer Tracking -->
	<script>
      (function(){
      mktr_key = "{/literal}{$addons.themarketer.tm_track_key}{literal}";
      var mktr = document.createElement("script"); mktr.async = true; mktr.src = "https://t.themarketer.com/t/j/" + mktr_key;
      var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(mktr,s);})();
	 
	</script>
    <!-- End Themarketer Tracking -->
  {/literal}
  {literal}
	<!-- Themarketer page name/data -->
	   <script>
	   window.dataLayer = window.dataLayer || [];
		var tm_page_name = "{/literal}{$_REQUEST['dispatch']}{literal}";
		//alert(tm_page_name);
		//start get page info
		if(tm_page_name == 'products.view'){
			dataLayer.push({
				event: "__sm__view_product",
				product_id: {/literal}{if $product.product_id != ''}{$product.product_id}{else}0{/if}{literal}
			});		
		} else if(tm_page_name == 'index.index'){
			dataLayer.push({
				event: "__sm__view_homepage"
			});		
		} else if(tm_page_name == 'suppliers.view'){
			dataLayer.push({
				event: "__sm__view_brand",
				name: "{/literal}{$supplier.supplier_id}{literal}"
			});		
		} else if(tm_page_name == 'checkout.complete'){
			
			{/literal}{$order_datalayer nofilter}{literal}		
		}	else if(tm_page_name == 'categories.view'){
			dataLayer.push({
				event: "__sm__view_category",
				category: "{/literal}{$category_names}{literal}"
			});	
			
		} else if(tm_page_name == 'products.search'){
			dataLayer.push({
				event: "__sm__search",
				search_term:  "{/literal}{$tm_search nofilter}{literal}"
			});		
		}{/literal}
		
		{literal}
		//add product to cart
		$('.ty-btn__add-to-cart').on('click',function(){
			var pid = $(this).attr('id');
			pid = pid.split('_');
			pid = pid[pid.length - 1];
			var qty = $('#qty_count_'+pid).val();
			$.post( "index.php?dispatch=themarketer.get_product", { product_id: pid, type: 'addtocart' }) 
			  .done(function( data ) {
				dataLayer.push({
					event: "__sm__add_to_cart",
					product_id: pid,
					quantity: qty,
					variation: {
						id: pid,
						sku: data
					}
			  });
			});
		}); 
		//remove product from cart
		$('.ty-cart-content__product-delete').on('click',function(){
			var pid = $(this).attr('href');
			pid = pid.split('cart_id=');
			pid = pid[1];
			pid = pid.split('&');
			pid = pid[0];
			var qty = $('#amount_'+pid).val();
			var productid = $('input[name="cart_products['+pid+'][product_id]"]').val()
			$.post( "index.php?dispatch=themarketer.get_product", { product_id: productid, type: 'removecart' }) 
			  .done(function( data ) {console.log(data);
				dataLayer.push({
					event: "__sm__remove_from_cart",
					product_id: productid,
					quantity: qty,
					variation: {
						id: productid,
						sku: data
					}
			  });
			});
		});
		
		//remove product from cart (checkout page)
		$('.ty-order-products__item-delete').on('click',function(){
			var pid = $(this).attr('href');
			pid = pid.split('cart_id=');
			pid = pid[1];
			pid = pid.split('&');
			pid = pid[0];
			$.post( "index.php?dispatch=themarketer.get_product", { cart_id: pid, type: 'getcart'}) 
			  .done(function( data ) {
			  var productdata = data.split('@');
				dataLayer.push({
					event: "__sm__remove_from_cart",
					product_id: productdata[0],
					quantity: productdata[2],
					variation: {
						id: productdata[0],
						sku: productdata[1]
					}			  
			  
			  });
			});
		});
		
		//add to wishlist
		$('.ty-add-to-wish').on('click',function(){
			var pid = $(this).attr('id');
			var pidc = pid.split('_');
			jQuery.post( "index.php?dispatch=themarketer.get_product", { product_id: pidc[2], type: 'addtowish' })
			  .done(function( productdata ) {console.log(productdata);
					dataLayer.push({
						event: "__sm__add_to_wishlist",
						product_id: pidc[2],
						variation: {
							id: pidc[2],
							sku: productdata
						}
					});
			});
		});
		//remove from wishlist
		$('.ty-twishlist-item__remove').on('click',function(){
			var formname = $(this).closest("form").attr('name');
			var pidc = formname.split('_');
			jQuery.post( "index.php?dispatch=themarketer.get_product", { product_id: pidc[2], type: 'removewish' })
			  .done(function( productdata ) {console.log(productdata);
					dataLayer.push({
						event: "__sm__remove_from_wishlist",
						product_id: pidc[2],
						variation: {
							id: pidc[2],
							sku: productdata
						}
					});
			});
		});
		
	//newsletter subscribe
	$('form[name="subscribe_form"] .ty-btn-go').on('click',function(){
		var nemail = $('form[name="subscribe_form"] .ty-input-text').val();
			$.post( "index.php?dispatch=themarketer.subscribenl", { email: nemail })
				  .done(function( subscribedata ) {
					console.log(subscribedata);
			})
			  .fail(function(err) {
				console.log(JSON.stringify(err, null, 4));
			  }); 
	});
	
	//check _initiate_checkout
	if(tm_page_name == 'checkout.checkout'){
		if(tmgetCookie('tm_initate_checkout') ==''){
			tmsetCookie('tm_initate_checkout', 1, 1);
			dataLayer.push({
				event: "__sm__initiate_checkout"
			});
		}
	}
	//themarketer cookies set-check
	function tmsetCookie(cname, cvalue, exdays) {
	  const d = new Date();
	  d.setTime(d.getTime() + (exdays*30*60*1000));
	  let expires = "expires="+ d.toUTCString();
	  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	function tmgetCookie(cname) {
	  let name = cname + "=";
	  let ca = document.cookie.split(';');
	  for(let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
		  c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
		  return c.substring(name.length, c.length);
		}
	  }
	  return "";
	}		
	  </script> 
	<!-- END Themarketer page name/data -->

  {/literal}  
{/if}
{if $smarty.session.tm.user_data.email != '' || $smarty.session.tm.user_data.email != 0}
{literal}
	<script>
			dataLayer.push({
				event: "__sm__set_email",
				email_address:{/literal}"{$smarty.session.tm.user_data.email}",
				firstname: "{$smarty.session.tm.user_data.firstname}",
				lastname: "{$smarty.session.tm.user_data.lastname}{literal}",
			});
			 
			dataLayer.push({
				event: "__sm__set_phone",
				phone:{/literal}'{$smarty.session.tm.user_data.phone}'{literal}
			});
	</script>
{/literal}
{$smarty.session.tm.email ='0'}
{/if}