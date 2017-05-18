{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="mfshifts" action="save"}
		{with model=$models.MFShift legend="MFShift Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='shift' class="compulsory" }
			{input type='date' attribute='shift_date' class="compulsory" }
			{select attribute='mf_dept_id' class="compulsory" options=$mfdepts}
			{select attribute='mf_centre_id' class="compulsory"  options=$mfcentres}
			{input type='text' attribute='comment' class="compulsory" }
		{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}