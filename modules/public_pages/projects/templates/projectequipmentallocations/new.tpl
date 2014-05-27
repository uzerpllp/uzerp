{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{form controller="projectequipmentallocations" action="save"}
		<dl id="view_data_left">
		{with model=$models.ProjectEquipmentAllocation}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl'}
			{if $model->project_id <> ''}
				{view_data attribute="project"}
			{/if}
			{select attribute="project_id"}
			{if $model->task_id <> ''}
				{view_data attribute="task"}
			{/if}
			{select attribute="task_id" force=true options=$tasks}
			{select attribute="project_equipment_id"}
			{input type='text' class='numeric' attribute='setup_charge' value=$setup_charge}
			{input type='text' class='numeric' attribute='charge_rate'  value=$charge_rate}
			{select attribute="charge_rate_uom_id"}
			{input type='date' attribute='start_date' value=$start_date}
			{input type='date' attribute='end_date' value=$end_date}
			{select attribute="charging_period_uom_id"}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/with}
		</dl>
		<dl id="view_data_right">
			{view_section heading="current_allocation"}
				<dd id='current_allocation' width=90%>
					{include file='elements/datatable_inline.tpl'}
				</dd>
			{/view_section}
		</dl>
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}