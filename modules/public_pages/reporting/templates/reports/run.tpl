{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper title=$title}
	{advanced_search}
	{data_table}
		<thead>
			<tr>
				{foreach key=key item=heading from=$headings}
					{* ATTN: does this work? *}
					{if $options.$key.normal_enable_formatting == 'true'}
						{assign var='class' value=$options.$key.normal_justify}
					{else}
						{assign var='class' value=''}
					{/if}
					<th class="{$class}" data-column="{$key}">{$heading}</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach key=row_key item=row from=$report_array}
				{* check subtotals *}
				{assign var=class value=''}
				{if $sub_total_keys.$row_key==true}
					{assign var=class value='sub_total'}
				{/if}
				<tr class="{$row_class}">
					{foreach key=cell_key item=cell from=$row}
						<td class="{$class}">{$cell}</td>
					{/foreach}
				</tr>
			{/foreach}
		</tbody>
	{/data_table}
{/content_wrapper}