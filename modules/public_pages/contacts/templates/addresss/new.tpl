{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.7 $ *}	
{content_wrapper}
	{form controller="addresss" action="save"}
		{with model=$models.Address legend="Address Details"}
			{view_section heading="Address Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='text'  attribute='street1' class="compulsory" }
				{input type='text'  attribute='street2' }
				{input type='text'  attribute='street3' }
				{input type='text'  attribute='town' class="compulsory" }
				{input type='text'  attribute='county' class="compulsory" }
				{input type='text'  attribute='postcode' class="compulsory" }
				{select attribute='countrycode' }
			{/view_section}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}