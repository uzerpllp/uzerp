{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="ticketreleaseversions" action="save"}
			{with model=$models.TicketReleaseVersion legend="Ticket Release Version Details"}
				<dl class="float-left">
					{view_section heading="ticket_details"}
						{input type='hidden' attribute='id' }
						{include file='elements/auditfields.tpl'}
						{input type='text' attribute='release_version'}
						{select attribute='status'}
						{textarea attribute='summary'}
						{input type='date' attribute='planned_release_date'}
						{input type='date' attribute='actual_release_date'}
					{/view_section}
				</dl>
				<dl class="float-right">
				</dl>
			{/with}
			<dl class="view_data_bottom">
				{submit}
			</dl>
		{/form}
		<dl class="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</dl>
	</div>
{/content_wrapper}