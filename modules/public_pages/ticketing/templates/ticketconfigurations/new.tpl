{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="ticketconfigurations" action="save"}
		{with model=$models.TicketConfiguration legend="Ticket Configuration Defaults"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl'}
			{if $action=='new'}
				{select attribute='company_id' forceselect=true}
			{else}
				{view_data attribute='company'}
			{/if}
			{select attribute='client_ticket_priority_id'}
			{select attribute='client_ticket_severity_id'}
			{select attribute='client_ticket_status_id'}
			{select attribute='internal_ticket_priority_id'}
			{select attribute='internal_ticket_severity_id'}
			{select attribute='internal_ticket_status_id'}
			{select attribute='ticket_category_id'}
			{select attribute='ticket_queue_id'}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}