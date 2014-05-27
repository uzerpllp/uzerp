{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="projectcostcharges" action="save"}
		{advanced_search}
		{paging}
		{assign var=templatemodel value=$collection->getModel()}
		{assign var=fields value=$collection->getHeadings()}
		{data_table}
			{heading_row}
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$templatemodel}
						{$heading}
					{/heading_cell}
				{/foreach}
				<th>Description</th>
				<th class='right'>Net Value</th>
				<th>Select</th>
			{/heading_row}
			{foreach name=datagrid item=costcharge from=$collection}
				{grid_row model=$model}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{grid_cell field=$fieldname model=$costcharge}
							{if ($costcharge->isEnum($fieldname))}
								{$costcharge->getFormatted($fieldname)}
       	    	    		{else}
								{$costcharge->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/foreach}
					{with model=$ProjectCostCharge}
						{input type='hidden' attribute='source_type' value=$source_type}
						{input type='hidden' attribute='source_id' value=$costcharge->id}
						{input type='hidden' attribute='quantity' value=$costcharge->$quantity}
						{input type='hidden' attribute='unit_price' value=$costcharge->$unit_price}
						{grid_cell field='description' model=$model no_escape=true}
						{if $source_type=='B'}
							{assign var=net_value value="{$costcharge->quantity * $costcharge->unit_price}"}
						{elseif $source_type=='E'}
							{assign var=net_value value="{$costcharge->total_charges}"}
						{elseif $source_type=='M'}
							{assign var=net_value value="{$costcharge->net_value}"}
						{elseif $source_type=='R'}
							{assign var=net_value value="{$costcharge->getNetValue()}"}
						{elseif $source_type=='X'}
							{assign var=net_value value="{$costcharge->net_value}"}
						{/if}						
							{input attribute='description' nolabel=true tags='none' value=$costcharge->$description}
						{/grid_cell}
						{grid_cell field='net_value' model=$model no_escape=true}
							{input attribute='net_value' nolabel=true tags='none' value=$net_value class='numeric net_value'}
						{/grid_cell}
						{grid_cell field='selected' model=$model no_escape=true}
							{input type='checkbox' attribute='selected' nolabel=true tags='none'}
						{/grid_cell}
					{/with}
				{/grid_row}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		{/data_table}
		{paging}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}