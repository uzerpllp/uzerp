{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="mfshiftoutputs" action="save"}
		{with model=$models.MFShiftOutput legend="MFShift Output Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='mf_shift_id'}
			{input type='hidden' attribute='mf_centre_id' value=$mfshift->mf_centre_id }
			{view_data model=$mfshift attribute=shift}
			{view_data model=$mfshift attribute=shift_date}
			{view_data model=$mfshift attribute=mf_dept}
			{view_data model=$mfshift attribute=mf_centre}
			{select attribute='stitem_id' class="compulsory" options=$stitems label='Stock Item'}
			{select attribute='uom_id' class="compulsory" options=$uoms}
			{input type='text' attribute='output' class="compulsory" }
			{input type='text' attribute='planned_time' class="compulsory" }
			{input type='text' attribute='run_time_speed' class="compulsory" value=$run_time_speed}
			{input type='text' attribute='operators' class="compulsory" }
			{select attribute='work_order_id' options=$work_orders nonone=true}
		{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}