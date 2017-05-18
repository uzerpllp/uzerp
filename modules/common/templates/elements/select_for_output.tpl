{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{content_wrapper}	
	{advanced_search action="select_for_output"}
	{form controller=$self.controller action='save_selection' notags=true}
		<input type='hidden' name='type' value={$type}>
		<input type='hidden' name='output_header_id' value={$output_header_id}>
		<input type='hidden' name='printer' value={$default_printer}>
		<div id="view_page" class="clearfix">
			<dl id="print_action" class="float-left">
				<dt><label for="printtype">Print Type</label>:</dt>
				<dd>
					<select id="printtype" name="printtype" >
						{html_options options=$printtype selected=$defaultprinttype}
					</select>
				</dd>
				{include file='elements/select_printer.tpl'}
				<dt><label for="filename">File Name</label>:</dt>
				<dd>
					<input type='text' name='filename' value={$filename}>
				</dd>
				<div id='csv' style=display:none>
					<dt><label for="fieldnames">Include Field Names</label>:</dt>
					<dd>
						<input type='checkbox' name="fieldnames" class='checkbox'>
					</dd>
					<dt><label for="fieldseparater">CSV Field Separater</label>:</dt>
					<dd>
						<select name="fieldseparater">
							{html_options options=$fieldseparater}
						</select>
					</dd>
					<dt><label for="textdelimiter">CSV Text Delimiter</label>:</dt>
					<dd>
						<select name="textdelimiter">
							{html_options options=$textdelimiter}
						</select>
					</dd>
				</div>
			</dl>
			<dl id='view_data_left'>
				Email Message:<br>
				<textarea cols=50 rows=5 name='emailtext'>{$emailtext}</textarea>
			</dl>
		</div>
		{paging}
		{assign var=count_selected value=0}
		{data_table class="select-for-output"}
			{heading_row}
				{foreach name=heading item=field from=$fields}
					{heading_cell model=$collection->getModel() field=$field}
						{$field|prettify}
					{/heading_cell}
				{/foreach}
				{heading_cell field='method'}
					Method
				{/heading_cell}
				{heading_cell field='print_copies'}
					Print Copies
				{/heading_cell}
				{heading_cell field='select'}
					Select
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$collection}
				{assign var=modelname value=$model->get_name()}
				{assign var=id value=$model->id}
				{grid_row model=$model}
					{foreach name=line item=field from=$fields}
						{if $field=='email'}
							<td>
								{if isset($selected_output.$id)}
									{input model=$model type="text" attribute="email" rowid="$id" number="$id" tags='none' nolabel=true value=$selected_output.$id.email}
								{else}
									{input model=$model type="text" attribute="email" rowid="$id" number="$id" tags='none' nolabel=true}
								{/if}
							</td>
						{else}
							{grid_cell model=$model field=$field}
								{if $smarty.foreach.line.index==0}
									{assign var="description" value=$model->$field}
								{/if}
								{$model->$field}
							{/grid_cell}
						{/if}
					{/foreach}
					<td align=left>
						{select model=$model force=true nonone=true attribute="printaction" rowid="$id" number="$id" tags='none' nolabel=true options=$printaction value=$selected_output.$id.printaction }
					</td>
					<td align=left>
						{input model=$model attribute="print_copies" rowid="$id" number="$id" tags='none' nolabel=true value=1 class="numeric"}
					</td>
					<td align=left>
						{if $selected_output.$id.select=='true'}
							{assign var=count_selected value=$count_selected+1}
							{input value="true" model=$model class="checkbox" type="checkbox" attribute="select" rowid="$id" number="$id" tags='none' nolabel=true }
						{else}
							{input model=$model class="checkbox" type="checkbox" attribute="select" rowid="$id" number="$id" tags='none' nolabel=true}
						{/if}
					</td>
					{input  model=$model type='hidden' attribute="description" rowid="$id" number="$id" value=$description}
				{/grid_row}
			{/foreach}
		{/data_table}
		<input type="hidden" id="link" value="{$link}">
		{paging}
		Select count <input type='text' readonly id=selected_count name=selected_count value="{$count_selected}" class='numeric'>
		{submit tags='none' name='save' value='Save Selection'}
	{/form}
	{form controller=$self.controller action='select_all' notags=true}
		<input type='hidden' name='type' value={$type}>
		<input type='hidden' name='id' value={$type_id}>
		{submit tags='none' name='save' value='Select All'}
	{/form}
	<script type="text/javascript">
		$(document).ready(function() {
			legacyForceChange('#printtype');
		});
	</script>
{/content_wrapper}