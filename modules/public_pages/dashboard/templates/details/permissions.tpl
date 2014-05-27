{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$User}
			<dl id="view_data_bottom">
				{view_section heading="Company Roles"}
					{foreach item=company from=$companies}
						{view_data value=$company->company}
						{assign var=usercompanyid value=$company->usercompanyid}
						<dl id="view_data_right">
							{foreach item=role from=$roles.$usercompanyid}
								{view_data value=$role->role}
							{/foreach}
						</dl>
					{/foreach}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}