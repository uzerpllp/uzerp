{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="mfdowntimecodes" action="save"}
		{with model=$models.MFDowntimeCode legend="MF Downtime Code Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text' attribute='downtime_code' class="compulsory" }
			{input type='text' attribute='description' class="compulsory" }
			{select model=$mfcentredowntimecode attribute='mf_centre_id' size="5" force=true nonone=true label='Work Centres' multiple=true options=$mf_centres value=$selected_centres}
		{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file="elements/cancelForm.tpl"}
	{if $mfdowntimecode->mf_centres->count()>0}
		<dl id="view_data_bottom">
		{data_table}
			{heading_row}
				{heading_cell}
					Assigned Work Centres
				{/heading_cell}
			{/heading_row}
			{foreach name=mf_centres item=mf_centre from=$mfdowntimecode->mf_centres}
				{grid_row model=$centre}
					{grid_cell model=$mf_centre cell_num=2 field="work_centre"}
						{$mf_centre->getIdentifierValue()}
					{/grid_cell}
				{/grid_row}
			{/foreach}
		{/data_table}
		</dl>
	{/if}
{/content_wrapper}