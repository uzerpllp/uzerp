{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
{content_wrapper}
	<b>Centre Details</b>
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="cost_centre"}
			{view_data model=$transaction attribute="description"}
		</dl>
		{if $transaction->accounts->count()>0}
			<dl id="view_data_bottom">
			{data_table}
				{heading_row}
					{heading_cell}
						Assigned Accounts
					{/heading_cell}
				{/heading_row}
				{foreach name=accounts item=account from=$transaction->accounts}
					{grid_row model=$account}
						{grid_cell model=$account cell_num=2 field="glaccount"}
							{$account->glaccount}
						{/grid_cell}
					{/grid_row}
				{/foreach}
			{/data_table}
			</dl>
		{/if}
	</div>
{/content_wrapper}