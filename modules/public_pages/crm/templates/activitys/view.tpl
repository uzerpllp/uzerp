{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$activity}
			<dl id="view_data_left">
				{view_section heading="Event Details"}
					{view_data attribute='name'}
					{view_data attribute='description'}
					{view_data attribute='owner'}
					{view_data attribute='assigned'}
					{view_data attribute='type_id'}
					{view_data attribute='company_id'}
					{view_data attribute='person_id'}
					{view_data attribute='opportunity_id'}
					{view_data attribute='campaign_id'}
					{view_data attribute='startdate'}
					{view_data attribute='enddate'}
					{view_data attribute='completed'}
					{view_data attribute='duration'}
				{/view_section}
				{view_section heading="Created/Amended"}
					{view_data attribute='created'}
					{view_data attribute='alteredby'}
					{view_data attribute='lastupdated'}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}