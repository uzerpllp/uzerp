{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Systemcompany}
			<dl id="view_data_left">
				{view_data attribute="company" value=$company->name}
				{view_data attribute="access_enabled"}
				{view_data attribute="audit_enabled"}
				{view_data attribute="published"}
				{view_data attribute="published_owner"}
			</dl>
			<dl id="view_data_right">
					{assign var=users_count value=$Systemcompany->users->count()}
					{view_section heading='Users ('|cat:$users_count|cat:')' expand='closed'}
						{foreach item=user from=$Systemcompany->users}
							{view_data model=$user label='' attribute='username'}
						{/foreach}
					{/view_section}
					{assign var=roles_count value=$Systemcompany->roles->count()}
					{view_section heading='Roles ('|cat:$roles_count|cat:')' expand='closed'}
						{foreach item=role from=$Systemcompany->roles}
							{view_data model=$role label='' attribute='name' link_to='"module":"admin","controller":"roles","action":"view","id":"'|cat:$role->id|cat:'"'}
						{/foreach}
					{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}
