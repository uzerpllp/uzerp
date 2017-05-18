{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$File}
				<dt class="heading">General</dt>
					{view_data attribute='name'}
					{view_data attribute='type'}
					{view_data attribute='size'}
			{/with}
		</dl>
	</div>
{/content_wrapper}