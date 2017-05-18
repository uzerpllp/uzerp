{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
	    <dl id="view_data_left">
	        {with model=$mfdept}
				{view_data attribute='dept_code'}
				{view_data attribute='dept'}
	        {/with}
	    </dl>
	</div>
	{include file="elements/datatable.tpl" collection=$mfcentres}
{/content_wrapper}