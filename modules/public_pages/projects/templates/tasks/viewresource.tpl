{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="tasks" action="deleteresource"}
	{input type='hidden' attribute='id'}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
				<dt class="heading">Resource Details</dt>
				<dt>Name:</dt><dd>{$resource.resource}</dd>
				<dt class="submit"></dt><dd class="submit"><input type="submit" name="remove_from_project" value="Remove" /></dd>
		</dl>
	</div>
	{/form}
{/content_wrapper}