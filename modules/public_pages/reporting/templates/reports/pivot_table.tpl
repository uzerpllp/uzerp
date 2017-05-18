{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
<!-- left pane -->
<div class="left-pane">
	<h3>Report Options</h3>
	{with model=$models.Report legend="Report Details"}
		<ul class="report_details">
			<li>
				<label>Report Name</label>
				{* ATTN: need to set selected values on these fields *}
				<input type="text" class="required uz-validate-required" value="{$description}" id="Report_description" name="Report[description]" />
				{* {input type='text' attribute='description' class="uz-validate-required"} *}
			</li>
			<li>
				<label>Report Table</label>
				{if $update}
					<strong> {$model->tablename}</strong>
					{input type='hidden' attribute='tablename'}
				{else}
					<select class="required" id="Report_tablename" name="Report[tablename]">
						{html_options options=$report_options selected=$selected_tablename}
					</select>
				{/if}
			</li>
			<li>
				<label>Report Definition</label>
				{html_options name="report_def" options=$report_definitions selected=$selected_reportdef}
				<p class="help-text">Choose a custom report format. Leave as default if unsure.</p>
			</li>
		</ul>
	{/with}

	<h3>Available Fields</h3>
	<ul id="fields_list" class="connectedSortable field_list">
		{foreach item=field key=sequence from=$available_fields}
			<li data-field-label="{$field->name}" data-field-type="{$field->type}" class="ui-state-default">{$field->name}</li>
		{/foreach}
	</ul>
	<fieldset>
		<button class="save clean-gray">Save</button>
		<button class="filter clean-gray">Filter</button>
	</fieldset>
</div>
<!-- middle pane -->
<div class="middle-pane" >
	<h3>Report Builder</h3>
	<h4>Rows</h4>
	<ul id="row_fields" class="connectedSortable field_list">
		{foreach key=field item=field_options from=$options}
			{if $field!='filter'}
				<li class="ui-state-default" data-field-type="{$field_options._field_data_type}" data-field-label="{$field}">{$field}</li>
			{/if}
		{/foreach}
	</ul>
</div>
<!-- right pane -->
<div class="right-pane">
	<h3>Properties</h3>
	<div id="properties">
		<p>Double click a field to see it's options</p>
	</div>
</div>