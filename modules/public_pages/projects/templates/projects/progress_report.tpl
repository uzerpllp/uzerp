{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller=$self.controller action="progress_report"}
		<dl id="report_filter">
			<dt>
				<label for="period_start">Start of reporting period:</label>
			</dt>
			<dd>
				<input type="text" class="datefield" name="period_start" id="period_start" />
			</dd>
			<dt>
				<label for="period_end">End of reporting period:</label>
			</dt>
			<dd>
				<input type="text" class="datefield" name="period_end" id="period_end" />
			</dd>
			<input type="hidden" name="id" value="{$id}" />
			<input type="submit" name="print" value="Print" />
		</dl>
	{/form}
{/content_wrapper}