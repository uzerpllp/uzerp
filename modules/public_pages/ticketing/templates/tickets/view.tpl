{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$Ticket}
				{view_section heading="General" expand='open'}
					{input type='hidden' attribute='id' }
					{view_data attribute='summary'}
					<dt>Number</dt><dd>{$Ticket->ticket_queue_id}-{$Ticket->id}</dd>
					{view_data label='Internal Status' attribute='internal_ticket_status'}
					{view_data label='Queue' attribute='ticket_queue'}
					{view_data label='Category' attribute='ticket_category'}
					{view_data label='Internal Severity' attribute='internal_ticket_severity'}
					{view_data label='Internal Priority' attribute='internal_ticket_priority'}	
					{view_data label='Assigned To' attribute='assigned_to'}
					{view_data attribute='release_version'}
				{/view_section}
			{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$Ticket}
				{view_section heading="Originator" expand='open'}
					{view_data attribute='originator_company'}
					{view_data attribute='originator_person'}
				{/view_section}
				{view_section heading="Timeline" expand='closed'}
						<dt>Duration</dt><dd>{$duration}</dd>
						{view_data label='Created at' attribute='created'}
						{view_data label='Updated at' attribute='lastupdated'}
				{/view_section}
				{view_section heading="Client Status" expand='closed'}
						{view_data label='Client Status' attribute='client_ticket_status'}
						{view_data label='Client Severity' attribute='client_ticket_severity'}
						{view_data label='Client Priority' attribute='client_ticket_priority'}
				{/view_section}
				{view_section heading="Change Log" expand='closed'}
						{view_data attribute='change_log' label=' ' label_position='above'}
				{/view_section}
			{/with}
		</dl>
	</div>
	<div id="ticket_responses">
		<ul>
			{foreach from=$responses item=response}
				{if $response->type == 'site'}
					<li class="site">
						<ul>
							<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) update from {$response->owner}</li>
							<li>{$response->body|h}</li>
						</ul>
					</li>
				{/if}
				{if $response->type == 'internal'}
					<li class="site">
						<ul>
							<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) <span style="color:red;font-weight:bold;">INTERNAL</span> update from {$response->owner}</li>
							<li>{$response->body|h}</li>
						</ul>
					</li>
				{/if}
				{if $response->type == 'email'}
					<li class="email">
						<ul>
							<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) received from {$response->email_address}<li>
							<li>{$response->body|h}</li>
						</ul>
					</li>
				{/if}
				{if $response->type == 'status'}
					<li class="status">
						<ul>
							<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) status change by {$response->owner}</li>
							<li>{$response->body|h|nl2br}</li>
						</ul>
					</li>
				{/if}
			{/foreach}
		</ul>
	</div>
	<div id="view_data_bottom">
		<button class="quick_response">Quick Response</button>
	</div>
{/content_wrapper}