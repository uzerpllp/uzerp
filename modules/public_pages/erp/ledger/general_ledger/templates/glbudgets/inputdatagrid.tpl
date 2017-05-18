{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.27 $ *}
{content_wrapper}
	{advanced_search}
	{if $errors!=true}
		<script type="text/javascript">
			var di_class_name = '{$do_name}';
			var search_centre = '{$search_centre}';
		</script>
		<table class="uz-data-input-spreadsheet" > 
			<tr>
				<th>&nbsp;</th>
				{foreach from=$columns item=column}
				    <th rel="{$column.id}">{$column.description}</th>
				{/foreach}
			</tr>
			{foreach from=$rows item=row}
				<tr rel="{$row.glaccount_id}">
					<td>{$row.glaccount}</td>
					{foreach from=$columns item=column}
					   	{strip}
							<td rel="{$data[$column.id][$row.glaccount_id].id}">
								{if $data[$column.id][$row.glaccount_id].value!=''}
									{$data[$column.id][$row.glaccount_id].value}
								{else}
									0
								{/if}
							</td>
						{/strip}
					{/foreach}
				</tr>
			{/foreach}
		</table>
		{form controller="glbudgets" action="save_data_input" notags=false }
			{foreach from=$basic_search item=search_value key=search_key}
				<input type="hidden" name="Search[{$search_key}]" value="{$search_value}" />
			{/foreach}
			{submit id='saveform' name='save' value='Save'}
		{/form}
	{/if}
{/content_wrapper}