{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Newsletter}
			<dl id="view_data_left">
				<dt class="heading">Newsletter Details</dt>
				{view_data attribute='name'}
				{view_data attribute='newsletter_url'}
				{view_data attribute='created'}
				{view_data attribute='send_at'}
				{view_data attribute='campaign'}
				{view_data attribute='owner'}
				{view_data attribute='total_views()'}
				{view_data attribute='total_unique_views()'}
				{view_data attribute='total_clicks()'}
			</dl>
		{/with}
		<dl id="view_data_right">
			{eglet name="NewsletterClicksByUrlGrapher" populate=true title="Click Distribution"}
		</dl>
	</div>
{/content_wrapper}