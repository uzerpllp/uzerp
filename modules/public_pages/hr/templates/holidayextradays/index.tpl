{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{include file="elements/options.tpl"}
	<div id="title_bar"><h1> Holiday Extra Days </h1></div>
	{search grid=$holidayextradays}
	
	{include file="elements/datatable.tpl" collection=$holidayextradays}
{/content_wrapper}