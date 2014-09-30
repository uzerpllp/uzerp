{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Opportunity}
		<dl id="view_data_left">
			<dt class="heading">Opportunity Details</dt>
				{view_data attribute='name'}
				{view_data attribute='company' link_to='"module":"contacts", "controller":"companys", "action":"view", "id":"'|cat:$model->company_id|cat:'"'}
				{view_data attribute='person' link_to='"module":"contacts", "controller":"persons", "action":"view", "id":"'|cat:$model->person_id|cat:'"'}
				{view_data attribute='source'}
				{view_data attribute='campaign'}
				{view_data attribute='status'}
				{view_data attribute='value'}
				{view_data attribute='cost'}
				{view_data attribute='probability'}
				{view_data attribute='enddate'}
				{view_data attribute='type'}
				{view_data attribute='nextstep'}
		</dl>
		<dl id="view_data_right">
			<dt class="heading">Access Details</dt>
				{view_data attribute='created'}
				{view_data attribute='lastupdated'}
			</dt>
		</dl>
		<dl id="view_data_fullwidth">
				{view_data attribute='description'}
		</dl>	
		{/with}
	</div>
{/content_wrapper}