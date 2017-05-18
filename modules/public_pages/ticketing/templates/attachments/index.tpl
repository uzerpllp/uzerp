{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{include file="elements/options.tpl"}
	<div id="title_bar"><h1> Attachment </h1></div>
	{search grid=$ticketattachments}	
	{include file="elements/datatable.tpl" collection=$ticketattachments}
{/content_wrapper}