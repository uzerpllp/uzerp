{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$model}
			<dl id="view_data_left">
				{assign var='title' value=$model->getIdentifierValue()}
				{view_section heading="$title"}
					{assign var=fields value=$model->getDisplayFieldNames()}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{view_data attribute=$fieldname label=$tag}
					{/foreach}
				{/view_section}
			</dl>
			{if $model->accounts->count()>0}
				<dl id="view_data_bottom">
				{data_table}
					{heading_row}
						{heading_cell}
							Assigned Accounts
						{/heading_cell}
					{/heading_row}
					{foreach name=accounts item=account from=$model->accounts}
						{grid_row model=$account}
							{grid_cell model=$account cell_num=2 field="glaccount"}
								{$account->glaccount}
							{/grid_cell}
						{/grid_row}
					{/foreach}
				{/data_table}
				</dl>
			{/if}
		{/with}
	</div>
{/content_wrapper}