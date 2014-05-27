{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.20 $ *}
{content_wrapper}
	{form controller="reports" action="save" }
		{with model=$models.Report legend="Report Details"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
		{/with}
		<div id="pivot_table">
			{include file="./pivot_table.tpl"}
		</div>
		<div id="options">
			{foreach key=field item=field_options from=$options}
				{foreach key=option_key item=option_value from=$field_options}
					<input type="hidden" class="field_option option_{$field}" id="{$option_key}_{$field}" name="Report[options][{$field}][{$option_key}]" value="{$option_value}" data-field-name="{$field}" />
				{/foreach}
			{/foreach}
		</div>
	{/form}
	{assign var=model value=$models.Report}

	<div id="filter_dialog" style="display: none;">
		<table>
			<thead>
				<tr>
					<th>Operator</th>
					<th>Field</th>
					<th>Condition</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td>
						<select name="filter_1_field" class="filter_field" id="filter_1_field"></select>
					</td>
					<td>
						<select name="filter_1_condition" class="filter_condition" id="filter_1_condition">
							<option value="=">=</option>
							<option value="<">&lt;</option>
							<option value=">">&gt;</option>
							<option value="<=">&lt;=</option>
							<option value=">=">&gt;=</option>
							<option value="<>">&lt;&gt;</option>
						</select>
					</td>
					<td><input name="filter_1_value" class="filter_value" id="filter_1_value" type="text" /></td>
					<td><img class="remove-filter" src="/themes/default/graphics/close.png" /></td>
				</tr>
				<tr>
					<td>
						<select name="filter_2_operator" class="filter_operator" id="filter_2_operator">
							<option value=""></option>
							<option value="AND">AND</option>
							<option value="OR">OR</option>
						</select>
					</td>
					<td>
						<select name="filter_2_field" class="filter_field" id="filter_2_field"></select>
					</td>
					<td>
						<select name="filter_2_condition" class="filter_condition" id="filter_2_condition">
							<option value="=">=</option>
							<option value="<">&lt;</option>
							<option value=">">&gt;</option>
							<option value="<=">&lt;=</option>
							<option value=">=">&gt;=</option>
							<option value="<>">&lt;&gt;</option>
						</select>
					</td>
					<td><input name="filter_2_value" class="filter_value" id="filter_2_value" type="text" /></td>
					<td><img class="remove-filter" src="/themes/default/graphics/close.png" /></td>
				</tr>
				<tr>
					<td>
						<select name="filter_3_operator" class="filter_operator" id="filter_3_operator">
							<option value=""></option>
							<option value="AND">AND</option>
							<option value="OR">OR</option>
						</select>
					</td>
					<td>
						<select name="filter_3_field" class="filter_field" id="filter_3_field"></select>
					</td>
					<td>
						<select name="filter_3_condition" class="filter_condition" id="filter_3_condition">
							<option value="=">=</option>
							<option value="<">&lt;</option>
							<option value=">">&gt;</option>
							<option value="<=">&lt;=</option>
							<option value=">=">&gt;=</option>
							<option value="<>">&lt;&gt;</option>
						</select>
					</td>
					<td><input name="filter_3_value" class="filter_value" id="filter_3_value" type="text" /></td>
					<td><img class="remove-filter" src="/themes/default/graphics/close.png" /></td>
				</tr>
			</tbody>
		</table>
	
	</div>
	
	<div id="row_options" style="display: none;">
		<form action="#">
			<div class="field_options options_pane" style="display: none;">
				<h4>Field Options</h4>
				<table>
					<tr>
						<td><label for="normal_field_label">Field Label:</label></td>
						<td><input type="text" id="normal_field_label" /></td>
					</tr>
					<tr>
						<td><label for="normal_display_field">Display Field?</label></td>
						<td><input type="checkbox" id="normal_display_field" checked="checked" /></td>
					</tr>
					<tr class="normal_display_field">
						<td><label for="normal_break_on">Break On:</label></td>
						<td><input type="checkbox" id="normal_break_on" /></td>
					</tr>
					<tr class="normal_display_field">
						<td><label for="normal_method">Method</label></td>
						<td>
							<select id="normal_method">
								{* theres no point in populating this list, as it's ajaxed when we open the options *}
							</select>
						</td>
					</tr>
					<tr class="normal_display_field">
						<td><label for="normal_total">Starting Total Level (L to R)</label></td>
						<td>
							<select id="normal_total">
								<option value="report">Report</option>
								<option value="none">None</option>
							</select>
						</td>
					</tr>
					<tr class="if_numeric normal_display_field">
						<td><label for="normal_enable_formatting">Enable Formatting?</label></td>
						<td><input type="checkbox" id="normal_enable_formatting" /></td>
					</tr>
					<tr class="if_numeric normal_formatting normal_display_field">
						<td><label for="normal_decimal_places">Decimals</label></td>
						<td>
							<select id="normal_decimal_places">
								<option value="0">0</option>
								<option value="1">1</option>
								<option value="2" selected >2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
							</select>
						</td>
					</tr>
					<tr class="if_numeric normal_formatting normal_display_field">
						<td><label for="normal_red_negative_numbers">Red negative numbers</label></td>
						<td><input type="checkbox" id="normal_red_negative_numbers" checked="checked" /></td>
					</tr>
					<tr class="if_numeric normal_formatting normal_display_field">
						<td><label for="normal_thousands_seperator">Thousands seperator</label></td>
						<td><input type="checkbox" id="normal_thousands_seperator" /></td>
					</tr>
					<tr class="normal_formatting normal_display_field">
						<td><label for="normal_justify">Justify</label></td>
						<td>
							<select id="normal_justify">
								<option value="left">Left</option>
								<option value="center">Center</option>
								<option value="right" selected="selected">Right</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="normal_enable_search">Enable Search?</label></td>
						<td><input type="checkbox" id="normal_enable_search" /></td>
					</tr>
					<tr class="normal_search">
						<td><label for="search_type">Operator:</label></td>
						<td>
							<select class="nonone" id="search_type"></select>
						</td>
					</tr>
					<tr class="normal_search">
						<td><label for="search_default_value">Default Value:</label></td>
						<td><input type="text" id="search_default_value" /></td>
					</tr>
				</table>
			</div>
			<fieldset>
				<button class="apply clean-gray">Apply</button>
				<button class="cancel clean-gray">Cancel</button>
			</fieldset>
		</form>
	</div>
{/content_wrapper}
{if $action!='edit'}
	<script type="text/javascript">
		$(document).ready(function() {
			$('#Report_description').focus();
		});
	 </script>
{/if}