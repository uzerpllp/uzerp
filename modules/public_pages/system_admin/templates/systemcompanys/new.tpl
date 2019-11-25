{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{form controller="systemcompanys" action="save"}
		<dl class="float-left">
			{view_section heading="System Company Details"}
				{with model=$models.Systemcompany legend="Systemcompany Details"}
					{if $model->id==''}
						{input type='text' attribute='name' label='Company'}
					{else}
						{input type='text' attribute='name' label='Company' value=$model->company readonly=true}
					{/if}
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='company_id' }
					{select attribute='access_enabled' }
					{input type='checkbox' attribute='audit_enabled'}
					{input attribute='info_message'}
					{if !is_null($model->logo_file_id)}
						{input type='checkbox' attribute='delete_logo' label='Delete Current Logo'}
						{assign var=label value='OR '}
					{/if}
					{input type='file' name='file' label=$label|cat:'upload new logo'}
				{/with}
			{/view_section}
		</dl>
		<dl class="float-right">
			{view_section heading="System Company Permissions"}
				{foreach item=permission from=$permissions}
					{input model=$permission type='checkbox' class='checkbox' attribute='permissions' rowid=$permission->id number=$permission->id label=$permission->title}
				{/foreach}
			{/view_section}
		</dl>
		<div id="view_data_bottom" class="clearfix">
			{submit}
		</div>
	{/form}
	<div id="view_data_bottom" class="clearfix">
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}
