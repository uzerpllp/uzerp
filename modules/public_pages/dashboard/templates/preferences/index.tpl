{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="preferences" action="save"}
		{with model=$models.UserPreferences legend="Preferences"}
			<dl id="view_data_left">
				{$templateCode}
			</dl>
		{/with}
		<dl class="view_data_left">
			{submit}
		</dl>
	{/form}
	<div id="view_page" class="clearfix">
		<dl class="view_data_left">
			{include file='elements/cancelForm.tpl'}
		</dl>
	</div>
{/content_wrapper}