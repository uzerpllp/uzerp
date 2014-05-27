{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
{content_wrapper}
	<b>Account Details</b>
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="account"}
			{view_data model=$transaction attribute="description"}
			{view_data model=$transaction label="Account Type" attribute="actype"}
			{view_data model=$transaction attribute="control"}
			{view_data model=$transaction attribute="analysis"}
		</dl>
		{if $transaction->centres->count()>0}
			<dl id="view_data_bottom">
			{data_table}
				{heading_row}
					{heading_cell}
						Assigned Cost Centres
					{/heading_cell}
				{/heading_row}
				{foreach name=centres item=centre from=$transaction->centres}
					{grid_row model=$centre}
						{grid_cell model=$centre cell_num=2 field="glcentre"}
							{$centre->glcentre}
						{/grid_cell}
					{/grid_row}
				{/foreach}
			{/data_table}
			</dl>
		{/if}
	</div>
{/content_wrapper}