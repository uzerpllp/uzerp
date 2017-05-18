{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="usercompanyaccesss" action="save"}
		{with model=$models.Usercompanyaccess legend="Usercompanyaccess Details"}
			{assign var='heading' value="User Company Access details for "|cat:$Usercompanyaccess->systemcompany->systemcompany->name}
			{view_section heading=$heading}
				{if $edit}
					{input type='hidden'  attribute='id' }
					<dt><label for="Usercompanyaccess[username]">Username:</label></dt><dd>{$models.Usercompanyaccess->username}</dd>
				{else}
					<dt><label for="Usercompanyaccess[username]">Username:</label></dt><dd>{html_options options=$users name="Usercompanyaccess[username]"}</dd>
				{/if}
				{input type='checkbox'  attribute='enabled' }
			{/view_section}
		{/with}
		{submit}
	{/form}
{/content_wrapper}