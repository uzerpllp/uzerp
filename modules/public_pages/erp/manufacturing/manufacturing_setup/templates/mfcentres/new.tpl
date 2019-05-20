{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="mfcentres" action="save"}
		{input type=hidden model=$mfdept attribute=production_recording}
		{with model=$models.MFCentre legend="MFCentre Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='mfdept_id'}
			{input type='text'  attribute='work_centre' class="compulsory" }
			{input type='text'  attribute='centre' class="compulsory" }
			{input type='text'  attribute='available_qty' class="compulsory" }
			{input type='text'  attribute='centre_rate' class="compulsory" }
			{if $mfdept->production_recording=='t'}
				{assign var=display value='block'}
			{else}
				{assign var=display value='none'}
			{/if}
			<div id='MFCentre_production_recording_label' style="display:{$display};">
				{input type='checkbox'  attribute='production_recording' class="compulsory" }
			</div>
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}