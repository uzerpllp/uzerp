{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$title}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$File}
				<dt class="heading">General</dt>
				{view_data attribute='name' link_to=$link}
				{view_data attribute='type'}
				{view_data attribute='size'}
				{view_data attribute='note'}
			{/with}
		</dl>
	</div>
{/content_wrapper}