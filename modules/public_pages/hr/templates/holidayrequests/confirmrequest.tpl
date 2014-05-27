{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="holidayrequests" action="save" notags=true}
		<div id="view_page" class="clearfix">
			{with model=$Holidayrequest legend="Holiday Request Details"}
				<dl>
					{view_section heading="Holiday Request Details"}
						{input type='hidden'  attribute='id' }
						{input type='hidden'  attribute='usercompanyid' }
						{input type='hidden'  attribute='employee_id'}
						{input type='hidden'  attribute='num_days'}
						{input type='hidden'  attribute='approved_by' value=$authoriser}
						{view_data attribute='start_date'}
						{view_data attribute='end_date'}
						{view_data attribute='num_days' label="Number of Days"}
						{view_data attribute='employee_notes'}
						{view_data attribute='special_circumstances'}
						{select attribute='status'}
						{input type='text'  attribute='reason_declined' notags=true}
					{/view_section}
				</dl>
			{/with}
		</div>
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}