{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$Ticket}
				<dt class="heading">General</dt>
					{view_data attribute='summary'}
					{view_data label='Status' attribute='client_ticket_status'}
	
					{view_data label='Queue' attribute='ticket_queue'}
					{view_data label='Category' attribute='ticket_category'}
	
					{view_data label='Severity' attribute='client_ticket_severity'}
					{view_data label='Priority' attribute='client_ticket_priority'}
			{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$Ticket}
				<dt class="heading">Originator</dt>
					{view_data fk_field='originator_person_id' attribute='originator_person'}
					{view_data fk_field='originator_company_id' attribute='originator_company'}
				<dt class="heading">Timeline</dt>
					{view_data label='Created at' attribute='created'}
					{view_data label='Updated at' attribute='lastupdated'}
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
							<li>{$response->body|wordwrap|h}</li>
						</ul>
				</li>
				{/if}
				{if $response->type == 'email'}
				<li class="email">
					<ul>
						<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) received from {$response->email_address}<li>
						<li>{$response->body|wordwrap|h}</li>
					</ul>
				</li>
				{/if}
				{if $response->type == 'status'}
				<li class="status">
					<ul>
						<li>({$response->created|date_format:'%d/%m/%y %H:%M'}) status change by {$response->owner}</li>
						<li>{$response->body|wordwrap|h|nl2br}</li>
					</ul>
				</li>
				{/if}
			{/foreach}
		</ul>
	</div>
	</div>
{/content_wrapper}