{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="setup_items">
		<h3>{$smarty.get.option|prettify}</h3>
		
		{form module=$module controller=$controller action=delete_items notags=true}
			<table id="setup_table" class="datagrid">
				<thead>
					<tr>
						<th></th>
						{foreach item=field key=fieldname from=$extrafields}
							{if $field.type != 'hidden'}
								<th align='left'>{$fieldname|prettify}</th>
							{/if}
						{/foreach}
						<th>Delete</th>
					</tr>
				</thead>
				
				<tfoot>
					<tr>
						<td colspan="0"><input type="submit" value="delete selected" /></td>
					</tr>
				</tfoot>
				
				<tbody id="setup_tbody">
					{foreach name=setup_items item=item from=$collection}
						{if $smarty.foreach.setup_items.index is even}
							{assign var=grid_row value='even'}
						{else}
							{assign var=grid_row value='odd'}
						{/if}
						<tr id="setuprow_{$item->id}">
							<td>{link_to module=$module controller=$controller action=edit option=$smarty.get.option id=$item->id value='edit'}</td>
							{foreach item=field key=fieldname from=$extrafields}
								{if $field.type != 'hidden'}
									<td>{$item->getFormatted($fieldname)}</td>
								{/if}
							{/foreach}
							<td class="narrow"><input type="checkbox" name="delete_items[{$smarty.get.option}][{$item->id}]" />
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/form}
	</div>
	
	<div id="setup_item_edit">
		<h2>
			{if $action == 'edit'}
				Edit
			{else}
				New
			{/if}
			Item
		</h2>
		{form module=$module controller=$controller action=save_item _option=$smarty.get.option notags=true}
			<table id="setup_edit_table">
				{with model=$model}
				{foreach name=edit_extrafields item=field key=name from=$edit_extrafields}
					<tr>
						<td>
							{if $field.type eq 'hidden'}
								{input type="hidden" attribute=$name}
							{elseif $field.type eq 'select'}
								{select attribute=$name options=$field.options selected=$field.value}
							{elseif $field.type eq 'bool'}
								{input type="checkbox" attribute=$name}
							{elseif $field.type != ''}
								{input attribute=$name}
							{/if}
						</td>
					</tr>	
				{/foreach}
				{/with}
				<tr>
					<td><input type="submit" value="save" /></td>
				</tr>
			</table>
		{/form}
	</div>
	
	<div id="setup_description">
		Add new items, edit existing ones, and re-order items.
	</div>
{/content_wrapper}