{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="tickets" action="save_response"}
		{with model=$models.TicketResponse}
			{view_section heading="response"}
				<textarea style="width: 98.7%; margin-top: 3px;" name="TicketResponse[body]" id="ticketresponse_body"></textarea>
				{input type='hidden' attribute='ticket_id' value=$models.TicketResponse->ticket_id}
				{input type='hidden' attribute='type' value='site'}
			{/view_section}
		{/with}
		<dl id="view_data_left">
			{view_section heading="Additional Details"}
				{with model=$models.TicketResponse}
					{input type='checkbox' attribute='internal' label='Internal only'}
				{/with}
				{input type='file' attribute='file' label='File Attachment'}
			{/view_section}
		</dl>
		<dl id="view_data_right">
			{with model=$models.Hour}
				{view_section heading="_"}
					{input type="hidden" attribute='ticket_id' value=$models.TicketResponse->ticket_id}
					{interval attribute='duration' value='0'}
					{select attribute="type_id"}
				{/view_section}
			{/with}
			{submit another='false'}
		</dl>
	{/form}
{/content_wrapper}