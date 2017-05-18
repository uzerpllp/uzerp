{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$title}
	{* {include file="elements/options.tpl"} *}
	{* {search grid=$fileattachments}	 *}
	{include file="elements/datatable.tpl" collection=$entityattachments}
{/content_wrapper}