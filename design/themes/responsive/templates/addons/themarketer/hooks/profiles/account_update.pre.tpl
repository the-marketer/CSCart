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