{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	{form controller="companycontactmethods" action="save"}
		<dl id="view_data_left">
			{with model=$models.Companycontactmethod legend="Companycontactmethod Details"}
				{view_section heading="Contact Method Details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='text'  attribute='name' class="compulsory" }
					{if $smarty.get.type eq 'T'}
						{input type='text'  attribute='contact' label='Telephone number' class="compulsory" }
					{elseif $smarty.get.type eq 'F'}
						{input type='text'  attribute='contact' label='Fax number' class="compulsory" }
					{elseif $smarty.get.type eq 'E'}
						{input type='text'  attribute='contact' label='Email address' class="compulsory" }
					{else}
						{input type='text'  attribute='contact' class="compulsory" }
					{/if}
					{input type='text'  attribute='type' class="compulsory" }
					{input type='checkbox'  attribute='main' }
					{input type='checkbox'  attribute='billing' }
					{input type='checkbox'  attribute='shipping' }
					{input type='checkbox'  attribute='payment' }
					{input type='checkbox'  attribute='technical' }
					{select attribute='company_id' }
				{/view_section}
			{/with}
			{submit}
		</dl>
	{/form}
	<div id="view_page" class="clearfix">
		<dl id="view_data_right">
			{include file="elements/cancelForm.tpl"}
		</dl>
	</div>
{/content_wrapper}