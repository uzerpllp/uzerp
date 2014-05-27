{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$TicketQueue}
				<dt class="heading">General</dt>
				{view_data attribute='name'}
				{view_data attribute='keywords'}
				{view_data attribute='queue_owner'}
				{view_data attribute='email_address'}
			{/with}
		</dl>
	</div>
{/content_wrapper}