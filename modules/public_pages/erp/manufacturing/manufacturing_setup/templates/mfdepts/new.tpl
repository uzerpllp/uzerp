{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="mfdepts" action="save"}
		{with model=$models.MFDept legend="MFDept Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='dept_code' class="compulsory" }
			{input type='text'  attribute='dept' class="compulsory" }
			{input type='checkbox'  attribute='production_recording' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}