{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="mfoperations" action="save"}
		{with model=$models.MFOperation legend="MFOperation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='op_no' class="compulsory" }
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{select label='Stock Item' attribute='stitem_id' options=$stitems}
			{select label='Work Centre' attribute='mfcentre_id' }
			{input type='text'  attribute='remarks' }
			{input type='text'  attribute='volume_target' }
			{select attribute=volume_uom_id options=$uom_list selected=$model->volume_uom_id}
			{select label='Per' attribute='volume_period' }
			{input type='text'  label="Quality Target(%)" attribute='quality_target' }
			{input type='text'  label="Uptime Target(%)" attribute='uptime_target' }
			{select label='Resource' attribute='mfresource_id' }
			{input type='text'  attribute='resource_qty' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
	<div id='show_parts'>
		{include file='./show_parts.tpl' action="cancel"}
	</div>
{/content_wrapper}