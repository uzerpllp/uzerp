{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.3 $ *}
	<div id="view_page" class="clearfix">
		{with model=$externalsystem}
			{view_data attribute="name"}
			{view_data attribute="description"}
		{/with}
	</div>
{/content_wrapper}