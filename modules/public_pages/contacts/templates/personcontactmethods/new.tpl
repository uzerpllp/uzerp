{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	{form controller="personcontactmethods" action="save"}
		{with model=$models.Personcontactmethod legend="Personcontactmethod Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='name' class="compulsory" }
			{input type='text'  attribute='contact' class="compulsory" }
			{input type='text'  attribute='type' class="compulsory" }
			{input type='checkbox'  attribute='main' }
			{input type='checkbox'  attribute='billing' }
			{input type='checkbox'  attribute='shipping' }
			{input type='checkbox'  attribute='payment' }
			{input type='checkbox'  attribute='technical' }
			{select attribute='person_id' }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}