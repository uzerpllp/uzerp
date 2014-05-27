{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.7 $ *}	
{content_wrapper}
	{form controller="partyaddresss" action="save"}
		{view_section heading="Address Details"}
			{with model=$models.PartyAddress legend="Partyaddress Details"}
				{with model=$model->address}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='text'  attribute='street1' class="compulsory" }
					{input type='text'  attribute='street2' }
					{input type='text'  attribute='street3' }
					{input type='text'  attribute='town' class="compulsory" }
					{input type='text'  attribute='county' class="compulsory" }
					{input type='text'  attribute='postcode' class="compulsory" }
					{select attribute='countrycode' }
				{/with}
				{with model=$PartyAddress}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='hidden'  attribute='address_id' }
					{input type='hidden' attribute='party_id' }
					{input type='text'  attribute='name' }
					{if $model->main=='t'}
						{view_data attribute='main' }
					{else}
						{input type='checkbox'  attribute='main' }
					{/if}
					{input type='checkbox'  attribute='billing' }
					{input type='checkbox'  attribute='shipping' }
					{input type='checkbox'  attribute='payment' }
					{input type='checkbox'  attribute='technical' }
				{/with}
			{/with}
		{/view_section}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}