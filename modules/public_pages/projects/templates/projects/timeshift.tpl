{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller=$self.controller action="timeshift" id=$smarty.get.id}
	<input type="hidden" name="id" value="{$smarty.get.id}"/>
	<dl id="report_filter">
		<dt class="heading">Time-shift project</dt>
		<dt>
			<label for="weeks">Weeks:</label>
		</dt>
		<dd>
			<input type="text" name="weeks" id="weeks" value="{$weeks}"/>
		</dd>
		<dt>
			&nbsp;
		</dt>
		<dd>
			<input type="submit" name="shift" value="Shift" />
		</dd>
	</dl>
	{/form}
{/content_wrapper}