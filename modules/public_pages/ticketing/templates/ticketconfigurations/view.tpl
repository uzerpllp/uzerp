{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$TicketConfiguration}
				{if $model->company_id==$smarty.const.COMPANY_ID}
					{view_data attribute='other' label='company' value='Default'}
				{else}
					{view_data attribute='company'}
				{/if}
				{view_data attribute='client_ticket_priority_default'}
				{view_data attribute='client_ticket_severity_default'}
				{view_data attribute='client_ticket_status_default'}
				{view_data attribute='internal_ticket_priority_default'}
				{view_data attribute='internal_ticket_severity_default'}
				{view_data attribute='internal_ticket_status_default'}
				{view_data attribute='ticket_category_default'}
				{view_data attribute='ticket_queue_default'}
			{/with}
		</dl>
	</div>
{/content_wrapper}