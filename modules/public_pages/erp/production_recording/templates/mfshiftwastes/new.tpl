{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="mfshiftwastes" action="save"}
		{with model=$models.MFShiftWaste legend="MFShift Waste Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='mf_shift_outputs_id' class="compulsory"}
			{select attribute='mf_centre_waste_type_id' class="compulsory" options=$waste_types force=true}
			{input type='text' attribute='uom_name' class="readonly" readonly=true label='UoM' value=$uom}
			{input type='text' attribute='qty' class="compulsory" }
		{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}