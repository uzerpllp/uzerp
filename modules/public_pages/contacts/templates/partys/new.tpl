{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	<dl id="view_data_left">
		{form controller="companys" action="save"}
			{with model=$models.Party legend="Company Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='hidden'  attribute='parent_id' }
				{input type='hidden'  attribute='type' }
				{view_section heading="account_details"}
					{with alias='company'}
						{input type='text'  attribute='name' class="compulsory" }
						{input type='text'  attribute='accountnumber' class="compulsory" }
						{select attribute='parent_id' ignore_parent_rel=true label='Parent Account'}
						{select attribute="assigned"}
					{/with}
				{/view_section}
				{view_section heading="contact_details"}
				{/view_section}
				{view_section heading="organisation_details"}
				{/view_section}
				{submit}
			{/with}
		{/form}
		{include file="elements/cancelForm.tpl"}
	</dl>		
{/content_wrapper}