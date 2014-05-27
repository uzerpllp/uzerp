{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="queues" action="save"}
		{with model=$models.TicketQueue legend="Queue Details"}
			{view_section heading="queue_details"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='usercompanyid'}
				{input type='text' attribute='name'}
				{input type='text' attribute='keywords'}
				{select attribute='queue_owner' options=$users}
				{input type='text' attribute='email_address'}
			{/view_section}
		{/with}
		{submit another='false'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}