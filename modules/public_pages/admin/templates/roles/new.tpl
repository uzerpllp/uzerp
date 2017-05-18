{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{form controller="roles" action="save"}
		<dl id="view_data_left" class="permissions">
			{with model=$models.Role legend="Role Details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='text'  attribute='name' class="compulsory" }
				{input type='checkbox'  attribute='manage_uzlets' }
				{textarea attribute='description' }
			{/with}
			{select model=$models.Role attribute="users" label="Users" multiple="multiple" options=$users value=$current_users nonone=true class="for_multiple"}
		</dl>
		<dl id="view_data_right">
			{view_section heading='Permissions'}
				<ul id="permission_tree" class="permissions collapsible_tree">
					{with model=$models.Role legend="Role Details"}
						{include file=$permissions_tree collection=$items name=permission parent_id=per class_name=permission roleid=$model->id}
					{/with}
				</ul>
			{submit}
			{/view_section}
		</dl>
	{/form}
{/content_wrapper}