{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<!DOCTYPE html>
<html>
	{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/head.tpl"}
	<body class="module-{$module|replace:'_':'-'} controller-{$controller|replace:'_':'-'}{if $action} action-{$action|ltrim:'_'|replace:'_':'-'}{/if}">
		{if isset($config['USE_NEW_MENU'])}
			{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/header-new-menu.tpl"}
		{else}
			{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/header.tpl"}
		{/if}
		{if isset($sideBarTemplateName)}
			<div id="content_with_sidebar">
				<div id="sidebar">
					{include file="$sideBarTemplateName"}
				</div>
				<div id="sidebar_open_close">
					<img src="/assets/graphics/application_side_contract.png" />
				</div>
				<div id="main_with_sidebar" class="rounded">
		{else}
				<div id="main_without_sidebar" class="rounded">
		{/if}
					{if isset($info_message) && $info_message}
						{include file="file:{$smarty.const.STANDARD_TPL_ROOT}elements/info_message.tpl"}
					{/if }
					<div id="included_file">
						{include file="$templateName"}
					</div>
				</div>
		{if isset($sideBarTemplateName)}
			</div>
		{/if}
		{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/footer.tpl"}
		<div id="additional_components">
			<div id="ajax_stage" style="display:none;"></div>
			<div id="original_print_dialog">
				<div class="print_wait">
					<p class="wait_title">Please Wait</p>
					<p class="wait_spinner"><img src="/assets/graphics/ajax_load.gif" /></p>
				</div>
				<div class="print_success">
					{* this screen will now be returned from the server *}
				</div>
				<div class="print_failure">
					<p class="wait_title">Output Failed</p>
					<p class="wait_message"></p>
					<p class="wait_spinner"><img src="/assets/graphics/large_error.png" /></p>
				</div>
			</div>
		</div>
	</body>
</html>
