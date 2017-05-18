{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="mfshiftdowntimes" action="save"}
		{with model=$models.MFShiftDowntime legend="MFShift Downtime Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='mf_shift_id' class="compulsory" }
			{select attribute='mf_centre_downtime_code_id' class="compulsory" options=$downtime_codes}
			{input type='text' attribute='down_time' class="compulsory" }
			{select attribute='time_period' class="compulsory"}
		{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}