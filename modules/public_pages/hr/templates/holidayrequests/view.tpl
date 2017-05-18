{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Holidayrequest}
			<dl class="float-left">
				{view_section heading="Holiday Request Details"}
					{view_data attribute='employee' label='Name'}
					{view_data attribute='start_date'}
					{view_data attribute='end_date'}
					{view_data value=$days_left label="Days Left"}
					{view_data attribute='num_days' label="Number of Days"}
					{view_data attribute='employee_notes'}
					{view_data attribute='special_circumstances'}
					{view_data attribute='status'}
					{view_data attribute='reason_declined'}
					{view_data attribute='authorised_by'}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}