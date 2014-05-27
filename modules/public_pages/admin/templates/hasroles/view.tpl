{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{include file="elements/options.tpl"}
	<div id="title_bar"><h1> HasRole </h1></div>
	{search grid=$hasroles}
	{include file="elements/datatable.tpl" collection=$hasroles}
{/content_wrapper}