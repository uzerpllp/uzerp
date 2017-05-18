{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	{form controller="companynotes" action="save"}
		{with model=$models.CompanyNote legend="CompanyNote Details"}
			<dl id="view_data_left">
				{view_section heading="Note Details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='hidden'  attribute='usercompanyid' }
					{input type='text'  attribute='title' class="compulsory" }
					{select attribute='company_id' }
					{textarea attribute='note' class="compulsory" }
				{/view_section}
			</dl>
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}