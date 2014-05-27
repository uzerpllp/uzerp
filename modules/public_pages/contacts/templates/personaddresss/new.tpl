{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	{form controller="personaddresss" action="save"}
		{with model=$models.Personaddress legend="Personaddress Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='street1' class="compulsory" }
			{input type='text'  attribute='street2' }
			{input type='text'  attribute='street3' }
			{input type='text'  attribute='town' class="compulsory" }
			{input type='text'  attribute='county' class="compulsory" }
			{input type='text'  attribute='postcode' class="compulsory" }
			{input type='text'  attribute='name' }
			{input type='checkbox'  attribute='main' }
			{input type='checkbox'  attribute='billing' }
			{input type='checkbox'  attribute='shipping' }
			{input type='checkbox'  attribute='payment' }
			{input type='checkbox'  attribute='technical' }
			{select attribute='countrycode' }
			{select attribute='person_id' }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}