{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$model}
			<dl id="view_data_left">
				<dt class="heading">Note Details</dt>
				{view_data attribute="title"}
				{view_data attribute="project"}
				{view_data attribute="type" label="Note Type"}
				{view_data attribute="owner"}
			</dl>
			<div id="view_data_fullwidth">
				{view_data attribute="note"}
			</div>
		{/with}
	</div>
{/content_wrapper}