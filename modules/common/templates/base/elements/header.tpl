{**
 *      (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *      Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.24 $ *}
<noscript>
	<div id="js_warning">
		<p>uzERP requires JavaScript, clear your temporary files to resolve or call a system administrator</p>
	</div>
</noscript>
<div id="header">
	{assign var=logoname value='/data/company'|cat:{$smarty.const.EGS_COMPANY_ID}|cat:'/logos/logo.png'}
	<img src={$systemcompany->get_logo($logoname)} id="logo" alt="EGS" />
	<div id="miniNav">
		<ul>
			<li><input type="button" id="messages_button" data-id="messages" style="display:none" value="Messages"/></li>
			<li><input type="button" id="warnings_button" data-id="warnings" style="display:none" value="Warnings"/></li>
			<li><input type="button" id="errors_button" data-id="errors" style="display:none" value="Errors"/></li>
			<li class="loading">Loading Data</li>
			{if $config.SYSTEM_MESSAGE !== '' }
				<li style="background:yellow;">{$config.SYSTEM_MESSAGE}</li>
			{/if}
			{if $help_link !== '' }
				<li class="help_link"><a href="{$help_link}" target="_new"><img id="image_help" src="assets/graphics/help.png">Help <span>Opens new window</span></a></li>
			{/if}
			{if $config.SYSTEM_STATUS !== '' }
				<li>{$config.SYSTEM_STATUS}</li>
			{/if}
			{if ($smarty.session.username)==''}
				<li>Not logged in</li>
				<li class="last">{link_to module="login" value="Login"}</li>
			{else}
				<li>Logged in to <strong>{link_to module="contacts" controller="companys" action="view" id=$smarty.const.COMPANY_ID value=$smarty.const.SYSTEM_COMPANY}</strong>
				 as <strong>{link_to module="dashboard" controller="details" value=$smarty.session.username}</strong></li>
				<li class="last">{link_to module="login" action="logout" value="Logout"}</li>
			{/if}
		</ul>
	</div>
	<div id="mainNav">
		{strip}
			{if isset($accessTree)}
				{include file="elements/main_nav.tpl" list=$accessTree.0 class='nav'}
			{/if}
		{/strip}
	</div>
</div>