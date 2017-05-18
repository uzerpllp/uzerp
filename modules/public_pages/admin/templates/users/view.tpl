{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$User}
			<dl class="float-left">
				{view_section heading="User Details" expand=open}
					{view_data attribute="username"}
					{view_data attribute="person"}
					{view_data attribute="email"}
					{view_data attribute="last_login"}
					{view_data value=$User->lastCompanyLogin() label='Last Company Login'}
					{view_data attribute="access_enabled"}
					{view_data attribute="audit_enabled"}
					{view_data attribute="debug_enabled"}
				{/view_section}
				{view_section heading="Company Roles" expand=open}
					{foreach item=company from=$companies}
						{view_section heading=$company->company}
							{assign var=usercompanyid value=$company->usercompanyid}
								{foreach item=role from=$roles.$usercompanyid}
									{view_data value=$role->role}
								{/foreach}
						{/view_section}
					{/foreach}
				{/view_section}
			</dl>
			<dl class="float-right">
				{foreach item=module_preferences key=module from=$preferences}
					{view_section heading=$module|cat:' Preferences' expand=open}
						{if $module=='shared'}
							{foreach item=preference key=name from=$module_preferences}
								{view_data value=$preference label=$name}
							{/foreach}
						{/if}
					{/view_section}
				{/foreach}
				{view_section heading="Available/Selected uzLETs" expand=open}
					{foreach key=module item=uzlets from=$dashboard.available}
						{view_section heading=$module|prettify expand=closed}
							{foreach item=uzlet key=key from=$uzlets}
								{if isset($dashboard.current.$module.$key)}
									{view_data value='*'|cat:$uzlet}
								{else}
									{view_data value=$uzlet}
								{/if}
							{/foreach}
							{link_to module=$module action="edit" value="Edit $module uzLETs" _id="edit_dashboard_link" username=$User->username}
						{/view_section}
					{/foreach}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}