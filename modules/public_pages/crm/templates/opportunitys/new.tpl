{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="opportunitys" action="save"}
		{with model=$models.Opportunity legend="Opportunity Details"}
			<dl id="view_data_left">
				{view_section heading="opportunity_details"}
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='usercompanyid' }
					{input type='text'  attribute='name' class="compulsory" }
					{input type='text'  attribute='value' }
					{input type='text'  attribute='cost' }
					{select attribute="probability"}
					{input type='date'  attribute='enddate' label='End date' }
					{input type='text'  attribute='nextstep' label='Next step' }
					{select attribute='status_id' }
					{select attribute='campaign_id' }
					{select attribute='company_id' constrains='person_id'}
					{select attribute='person_id' depends='company_id'}
					{select attribute='source_id' }
					{select attribute='type_id' }
					{select attribute="assigned"}
					{textarea  attribute='description' }
					{submit}
					{include file='elements/saveAnother.tpl'}
				{/view_section}
			</dl>
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}