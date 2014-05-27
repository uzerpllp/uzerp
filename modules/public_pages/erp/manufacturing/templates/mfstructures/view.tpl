{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			<dt class="heading">Current Structure</dt>
			{view_data model=$transaction attribute="line_no" id=''}
			{view_data model=$transaction label="Parent Item" attribute="stitem" id=''}
			{view_data model=$transaction attribute="start_date" id=''}
			{view_data model=$transaction attribute="end_date" id=''}
			{view_data model=$transaction label="Part Item" attribute="ststructure" id=''}
			{view_data model=$transaction attribute="remarks" id=''}
			{view_data model=$transaction label="Quantity" attribute="qty" id=''}
			{view_data model=$transaction label="UOM" attribute="uom" id=''}
			{view_data model=$transaction label="Waste %" attribute="waste_pc" id=''}
		</dl>
		{if $showform}
			{form controller="mfstructures" action="substitute" notags=false}
			<input type="hidden" name="MFStructure[current_structure_id]" value="{$transaction->id}" />
			<input type="hidden" name="MFStructure[id]" value="{$substitute->id}" />
			{*input type='hidden' model=$substitute attribute='id' *}
			{input type='hidden' model=$transaction attribute='usercompanyid' }
			{input type='hidden' model=$transaction attribute='line_no' }
			{input type='hidden' model=$transaction attribute='stitem_id' }
			<dl id="view_data_right">
				<dt class="heading">New Structure</dt>
				{view_data model=$transaction attribute="line_no"}
				{view_data model=$transaction label="Parent Item" attribute="stitem"}
				{input type='date' model=$substitute attribute='start_date' }
				{input type='date' model=$substitute attribute='end_date' }
				{select model=$substitute attribute='ststructure_id' options=$ststructures label='Part Item' }
				{input type='text' model=$substitute attribute='remarks' }
				{input type='text' model=$substitute attribute='qty' }
				{select model=$substitute attribute='uom_id' options=$uom_list selected=$uom_id}
				{input type='text' model=$substitute label="Waste %" attribute='waste_pc' }
				<dt class="submit"></dt>
				<dd class="submit">{submit value="Save Substitution" tags="none"}</dd>
			</dl>
			{/form}
		{/if}
	</div>
{/content_wrapper}