{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$model}
				{view_data attribute="title" }
				{view_data attribute="start_time" }
				{view_data attribute="end_time" }
				{view_data attribute="status" }
			{/with}
		</dl>
	</div>
{/content_wrapper}