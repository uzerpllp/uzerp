{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{with model=$LoggedCall}
		{form module=$module controller=$controller action=save}
			<dl id="view_data_left">
				{view_section heading="call_details"}
					{input type="hidden" attribute="id"}
					{select attribute="company_id"}
					{if !empty($company)}
						{view_data attribute='company' value=$company}
					{/if}
					{select attribute="person_id"}
					{if !empty($person)}
						{view_data attribute='person_id' value=$person}
					{/if}
					{input attribute="subject"}
					{select attribute="direction"}
					{select attribute="parent_id"}
					{datetime class='date' attribute="start_time"}
					{datetime class='date' attribute="end_time"}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="related_to"}
					{if $controller_data.project_id}
						{select attribute="project_id" data=$projects}
					{elseif $controller_data.opportunity_id}
						{select attribute="opportunity_id" data=$opportunities}
					{elseif $controller_data.activity_id}
						{select attribute="activity_id" data=$activities}
					{else}
						{select attribute="project_id" data=$projects}
						{select attribute="opportunity_id" data=$opportunities}
						{select attribute="activity_id" data=$activities}
					{/if}
				{/view_section}
				{view_section heading="notes"}
					{textarea attribute="notes" label=''}
				{/view_section}
				{view_section heading=""}
					{submit}
				{/view_section}
			</dl>
		{/form}
		<div id="view_page" class="clearfix">
			<dl id="view_data_right">
				{include file="elements/cancelForm.tpl"}
			</dl>
		</div>
	{/with}
{/content_wrapper}