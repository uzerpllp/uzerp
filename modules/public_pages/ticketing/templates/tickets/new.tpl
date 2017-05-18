{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="tickets" action="save"}
			{with model=$models.Ticket legend="Ticket Details"}
				<dl class="float-left">
					{view_section heading="ticket_details"}
						{input type='hidden' attribute='id' }
						{input type='hidden' attribute='usercompanyid'}
						{input type='text' attribute='summary'}
			
						{input type='hidden' attribute='originator_company_id' readonly=true}
						{input type='text'  value=$originator_company readonly=true class="readonly" label='Company'}
						{select attribute='originator_person_id' label='Contact' options=$people}

						{input type='text' attribute='originator_email_address' label='contact email address' value=$email}
						{input type='text' attribute='raised_by' readonly=true class="readonly" label='Raised By'}
			
						{select attribute='client_ticket_status_id' label='Client Status' value=$client_ticket_status_default}
						{select attribute='internal_ticket_status_id' label='Internal Status' value=$internal_ticket_status_default}
			
						{select attribute='ticket_queue_id' label='Queue' value=$ticket_queue_default}
			
						{select attribute='ticket_category_id' label='Category' value=$ticket_category_default}
	
						{select attribute='client_ticket_severity_id' label='Client Severity' value=$client_ticket_severity_default}
						{select attribute='internal_ticket_severity_id' label='Internal Severity' value=$internal_ticket_severity_default}
					
						{select attribute='client_ticket_priority_id' label='Client Priority' value=$client_ticket_priority_default}
						{select attribute='internal_ticket_priority_id' label='Internal Priority' value=$internal_ticket_priority_default}
			
						{select attribute='assigned_to' label='Assigned To'}
						{* {input type='hidden' attribute='action' value=$action} *}
					{/view_section}
				</dl>
				<dl class="float-right">
					{view_section heading="change log"}
						{select attribute='ticket_release_version_id' label='Release Version' force=true}
						{textarea attribute="change_log" tags='none'}
					{/view_section}
					<br>
					{view_section heading="description"}
						{with model=$models.TicketResponse}
							{input type='hidden' attribute='ticket_id' value=$Ticket->id}
							{textarea attribute="body" tags='none'}
							{input type='hidden' attribute='type' value='site'}
							{input type='checkbox' attribute='internal'}
						{/with}
					{/view_section}
				</dl>
				<dl class="view_data_bottom">
					{submit}
				</dl>
			{/with}
		{/form}
		<dl class="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</dl>
	</div>
{/content_wrapper}