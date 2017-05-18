{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Role}
			<dl class="float-left">
				{view_data attribute="name"}
				{view_data attribute="description"}
				{view_data attribute="manage_uzlets"}
				{assign var=users_count value=$users|count}
				{view_section heading='Users ('|cat:$users_count|cat:')' expand='closed'}
					{foreach item=user from=$users}
						{view_data label='' value=$user link_to='"module":"admin","controller":"users","action":"view","username":"'|cat:$user|cat:'"'}
					{/foreach}
				{/view_section}
				{assign var=reports_count value=$reports|count}
				{view_section heading='Reports ('|cat:$reports_count|cat:')' expand='closed'}
					{foreach item=report from=$reports}
						{view_data label='' value=$report}
					{/foreach}
				{/view_section}
			</dl>
			<dl class="float-right">
				{view_section heading='Permissions' expand='open'}
					<ul id="permission_tree" class="permissions collapsible_tree">
						{with model=$models.Role legend="Role Details"}
							{include file=$permissions_tree collection=$items name=permission parent_id=per class_name=permission roleid=$model->id}
						{/with}
					</ul>
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}