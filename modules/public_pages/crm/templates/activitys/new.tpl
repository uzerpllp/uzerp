{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="activitys" action="save"}
		{with model=$models.Activity legend="Activity Details"}
			<dl id="view_data_left">
				{view_section heading="activity_details"}
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='usercompanyid' }
					{select  attribute='opportunity_id' }
					{input type='text'  attribute='name' class="compulsory" force=true}
					{input type='date'  attribute='startdate' }
					{input type='date'  attribute='enddate' }
					{input type='date'  attribute='completed' }
					{input type='text'  attribute='duration' }
					{select attribute='type_id' }
					{select attribute='campaign_id' }
					{select attribute='company_id' constrains='person_id'}
					{select attribute='person_id' depends='company_id' }
					{select attribute='assigned'}
					{textarea  attribute='description' force=true}
					{submit}
				{/view_section}
			</dl>
		{/with}
	{/form}
    {include file="elements/cancelForm.tpl"}
{/content_wrapper}