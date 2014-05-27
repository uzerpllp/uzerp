{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	{include file="file:{$smarty.const.THEME_ROOT}{$theme}/elements/head.tpl"}
	<body>
		{include file="file:{$smarty.const.THEME_ROOT}{$theme}/elements/header.tpl"}
		{if isset($sideBarTemplateName)}
			<div id="content_with_sidebar">
				<div id="sidebar">
					{include file="$sideBarTemplateName"}
				</div>
				<div id="sidebar_open_close">
					<img src="/themes/{$theme}/graphics/application_side_contract.png" />
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
		{include file="file:{$smarty.const.THEME_ROOT}{$theme}/elements/footer.tpl"}
		<div id="additional_components">
			<div id="ajax_stage" style="display:none;"></div>
			<div id="original_print_dialog">
				<div class="print_wait">
					<p class="wait_title">Please Wait</p>
					<p class="wait_spinner"><img src="/themes/{$theme}/graphics/ajax_load.gif" /></p>
				</div>
				<div class="print_success">
					{* this screen will now be returned from the server *}
				</div>
				<div class="print_failure">
					<p class="wait_title">Output Failed</p>
					<p class="wait_message"></p>
					<p class="wait_spinner"><img src="/themes/{$theme}/graphics/large_error.png" /></p>
				</div>
			</div>
		</div>
	</body>
</html>