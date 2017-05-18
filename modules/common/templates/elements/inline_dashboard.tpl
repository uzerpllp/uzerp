{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<div class="{$uzlet_class} eglet_include">
	{view_section heading=$uzlet_title|prettify expand="open"}
		<div class="eglet_body" data-name="{$egletname}">
			{$uzlet->render()}
		</div>
	{/view_section}
</div>