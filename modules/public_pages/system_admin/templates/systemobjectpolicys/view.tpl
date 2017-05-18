{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$SystemObjectPolicy}
			<dl id="view_data_left">
				{view_data attribute="name"}
				{view_data label='component' attribute='getComponentTitle()' link_to='"module":"'|cat:$module|cat:'", "controller":"modulecomponents", "action":"view", "id":"'|cat:$model->module_components_id|cat:'"'}
				{if $model->is_id_field=='t'}
					{view_data attribute="operator"}
					{view_data attribute="value" value=$model->getValue()}
				{else}
					{view_data attribute="fieldname" value=$model->get_field()}
					{view_data attribute="operator"}
					{view_data attribute="value" value=$model->getValue()}
				{/if}
			</dl>
		{/with}
	</div>
	<div id="view_page" class="clearfix">
		{view_section heading='Policy_Permissions'}
		{/view_section}
		{assign var=fields value=$systempolicycontrollists->getHeadings()}
		{data_table}
			{heading_row}
				{with model=$systempolicycontrollists->getModel()}
					{heading_cell field='allowed' model=$model}
						Access
					{/heading_cell}
					{foreach name=headings item=heading key=fieldname from=$fields}
						{if $fieldname!='system_policy' && $fieldname!='allowed'}
							{heading_cell field=$fieldname model=$model}
								{$heading}
							{/heading_cell}
						{/if}
					{/foreach}
				{/with}
			{/heading_row}
			{foreach name=datagrid item=model from=$systempolicycontrollists}
				{grid_row model=$model}
					<td class="edit-line">
						{if $model->allowed=='t'}
							{assign var=permission value='Allow'}
						{else}
							{assign var=permission value='Deny'}
						{/if}
						{link_to module=$module controller='systempolicycontrollists' action='edit' id=$model->id value=$permission}
					</td>
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{if $fieldname!='system_policy' && $fieldname!='allowed'}
							{grid_cell field=$fieldname cell_num=2 model=$model collection=$systempolicycontrollists}
								{if ($model->isEnum($fieldname))}
									{$model->getFormatted($fieldname)}
								{else}
									{$model->getFormatted($fieldname)}
								{/if}
							{/grid_cell}
						{/if}
					{/foreach}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
	</div>
{/content_wrapper}