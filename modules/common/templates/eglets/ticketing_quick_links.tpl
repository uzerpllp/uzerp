{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<dl>
	{*<dt>{"ticket_views"|prettify}</dt>
		<dd>{link_to module="ticketing" controller="tickets" action="index" mode="mytickets" value="my_tickets"} &raquo;</dd>
		<dd>{link_to module="ticketing" controller="tickets" action="index" mode="newtickets" value="new_tickets"} &raquo;</dd>
		<dd>{link_to module="ticketing" controller="tickets" action="index" mode="activetickets" value="active_tickets"} &raquo;</dd>
	*}
	{if $access->hasPermission('ticketing','tickets')}
	<dt>{"other_ticket_actions"|prettify}</dt>
		<dd>{link_to module="ticketing" controller="tickets" value="all_tickets"} &raquo;</dd>
		<dd><img src="/assets/graphics/new_small.png" alt="New" />{link_to module="ticketing" controller="tickets" action="new" value="add_new_ticket"} &raquo;</dd>
	{else}
	<dt>{"client_ticket_actions"|prettify}</dt>
		<dd>{link_to module="ticketing" controller="client" value="my_tickets"} &raquo;</dd>
		<dd>{link_to module="ticketing" controller="client" action="new" value="new_ticket"} &raquo;</dd>
	{/if}
</dl>
