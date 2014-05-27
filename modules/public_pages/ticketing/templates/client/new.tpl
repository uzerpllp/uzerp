{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="client" action="save"}
		{with model=$models.Ticket legend="Ticket Details"}
			<dl id="view_data_left">
				{view_section heading="ticket_details"}
					{input type='hidden' attribute='id' }
					{input type='hidden' attribute='usercompanyid'}
					{input type='text' attribute='summary'}
					{select attribute='ticket_queue_id' label='Queue'}
					{select attribute='ticket_category_id' label='Category'}
				{/view_section}
			</dl>
			<dl id="view_data_left">
				{view_section}
					{select attribute='client_ticket_status_id' label='Status'}
					{select attribute='client_ticket_severity_id' label='Severity'}
					{select attribute='client_ticket_priority_id' label='Priority'}
				{/view_section}
			</dl>
		{/with}
		{with model=$models.TicketResponse}
			<div id="view_data_fullwidth">
				{if $new_ticket}
					{view_section heading="description"}
						<textarea style="width: 98.7%; margin-top: 3px;" name="TicketResponse[body]" id="ticketresponse_body"></textarea>
						{input type='hidden' attribute='ticket_id' value=$models.TicketResponse->ticket_id}
						{input type='hidden' attribute='type' value='site'}
					{/view_section}
				{/if}
				{submit another='false'}
			</div>
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}