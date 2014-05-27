{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
<form method="POST" id="spreadsheet_form">
	<input type="hidden" name="doc" value="{$doc}"/>
	<ul class="spreadsheet_actions">
		<li class="save_action">Save</li>
		<li class="duplicate_action">Duplicate</li>
		<li class="delete_action">Delete</li>
		<li class="add_action">Add Row</li>
		<li class="search_action">Search</li>	
	</ul>
	<table id="spreadsheet">
	<thead>
		<tr>
		<th class="control"></th>
		{foreach from=$columns item=col}
			{if $col.name != 'id'}
				<th class="col">{$col.heading}</td>
			{/if}
		{/foreach}
		<th class="indicator"></th>
		</tr>
		<tr class="search">
			<td class="control"></td>
			{foreach from=$columns item=col}
			
			{if $col.name != 'id'}
			<td>{if $col.type == 'select'}
					<select name="search[{$col.name}]">
						<option value="" class="empty"></option>
						{foreach from=$col.options key=k item=option}
							<option value="{$k}" autocomplete="off">{$option}</option>
						{/foreach}
					</select>
				{elseif $col.type == 'date'}
					<input type="text" name="search[{$col.name}]" class="{$col.type}"  autocomplete="off"/>
				{else}
					<input type="text" name="search[{$col.name}]" class="{$col.type}" autocomplete="off"/>
				{/if}</td>
			{/if}
		{/foreach}
		<td class="indicator"></td>
		</tr>
	<tbody>
	{foreach from=$rows item=row}
		<tr id="row-{$row.id}" class="data">
			<td class="control"><input type="checkbox" name="row[{$row.id}][select]" value="{$row.id}"/></td>
		{foreach from=$columns item=col}
			{assign var=colname value=$col.name}
			{if $col.name != 'id'}
			<td class="column">
				{if $col.type == 'select'}
					<select name="row[{$row.id}][{$colname}]">
						{foreach from=$col.options key=k item=option}
							<option value="{$k}" {if $row.$colname == $k}selected='selected'{/if} autocomplete="off">{$option}</option>
						{/foreach}
					</select>
				{elseif $col.type == 'date' OR $col.type == 'float' OR $col.type=='int'}
					<input type="text" name="row[{$row.id}][{$colname}]" class="{$col.type}" value="{$row.$colname|stripslashes}" autocomplete="off"/>
				
				{else}
					<input type="text" name="row[{$row.id}][{$colname}]" class="{$col.type}" value="{$row.$colname|stripslashes}" maxlength="{$col.max_length}" autocomplete="off"/>
				{/if}
			</td>
			{/if}
		{/foreach}
			<td class="indicator"></td>
		</tr>
	{/foreach}
	</tbody>
	</thead>
	</table>
	<ul class="spreadsheet_actions">
		<li class="save_action">Save</li>
		<li class="duplicate_action">Duplicate</li>
		<li class="delete_action">Delete</li>
		<li class="add_action">Add Row</li>
		<li class="search_action">Search</li>
	</ul>
</form>
<script type="text/javascript">
	Doc.pid={$pid};
	Doc.fields={$json_columns};
</script>