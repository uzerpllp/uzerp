{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	<div id="{page_identifier}">
		{form controller="datamappingdetails" action="save"}
			{with model=$models.DataMappingDetail legend="Data Mapping Details"}
				{input type='hidden' attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='hidden' attribute='data_mapping_rule_id' value=$data_mapping_rule_id}
				{select attribute='parent_id' label=$parent_label force=true options=$parent_options value=$mapping_id}
				{input type='text' attribute='external_code' class="compulsory" }
				{select attribute='internal_code' label=$internal_name options=$internal_codes nonone=true}
			{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id="view_page" class="clearfix">
		{view_section heading='Current Values'}
			{paging}
			{data_table}
				{heading_row}
					{heading_cell field="external_code"}
						External Value
					{/heading_cell}
					{heading_cell field="internal_code"}
						Internal Value
					{/heading_cell}
				{/heading_row}
				{foreach name=datagrid item=model from=$datamappingdetails}
					{grid_row model=$model}
						{grid_cell model=$model cell_num=1 field="external_code"}
							{$model->external_code}
						{/grid_cell}
						{grid_cell model=$model cell_num=2 field="internal_code"}
							{$model->displayValue()}
						{/grid_cell}
					{/grid_row}
				{foreachelse}
					<tr>
						<td colspan="0">No matching records found!</td>
					</tr>
				{/foreach}
			{/data_table}
			{paging}
		{/view_section}
	</div>
{/content_wrapper}