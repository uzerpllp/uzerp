{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="csfailurecodes" action="save"}
			{with model=$models.CSFailureCode legend="CSFailureCode Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='text'  attribute='code' }
				{input type='text'  attribute='description' class="compulsory" }
				{input type='checkbox'  attribute='in_use' class="compulsory" }
				{input type='checkbox'  attribute='non_failure' class="compulsory" }
			{/with}
		{submit}
		{include file="elements/saveAnother.tpl"}
	{/form}
	{include file='elements/cancelForm.tpl' action='index'}
{/content_wrapper}