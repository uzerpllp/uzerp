{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller=$self.controller action="summary_report"}
		<dl id="report_filter">
			<dt>
				<label for="filter_from">From:</label>
			</dt>
			<dd>
				<input type="text" class="datefield" name="from_date" id="filter_from" />
			</dd>
			<dt>
				<label for="filter_to">To:</label>
			</dt>
			<dd>
				<input type="text" class="datefield" name="to_date" id="filter_to" />
			</dd>
			{if isModuleAdmin()}
			<dt>
				<label for="filter_assigned">Assigned to:</label>
			</dt>
			<dd>
				<select id="filter_assigned" name="assigned">
					<option value="">All</option>
					{html_options options=$users}
				</select>
			{/if}
			<input type="submit" name="filter" value="Filter" />
		</dl>
	{/form}
	<table id="report_preview">
		{assign var=colspan value=$report_headings|@count}
		{assign var=colspan value=$colspan-2}
		{foreach name=statuses item=status from=$statuses}
			<tr class="report_heading">
				<td colspan="{$colspan}">
					<strong>Sales Stage:</strong><em>{$status->name}</em>
				</td>
				<td>
					<strong>Total Sales:</strong>
				</td>
				<td>
					<em>{$status->getTotalCost($cc)|pricify}</em>
				</td>
			</tr>
			<tr class="report_titles">
				{foreach name=headings item=heading from=$report_headings}
					<td>{$heading|prettify}</td>
				{/foreach}
			</tr>
			{foreach name=os item=opp from=$status->opportunities}
				<tr class="report_row">
					{foreach name=headings item=heading from=$report_headings}
						{assign var=value value=$opp->getField($heading)}
						<td>{$value->formatted()}</td>
					{/foreach}
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">
						No records
					</td>
				</tr>
			{/foreach}
		{/foreach}
	</table>
	<div id="print_header">
		<img src="/data/company{$smarty.const.EGS_COMPANY_ID}/logos/logo.png" id="logo" alt="EGS" />
		<dl>
			<dt>From:</dt>
			<dd>{$controller_data.from_date}</dd>
			<dt>Until:</dt>
			<dd>{$controller_data.to_date}</dd>
			<dt>Assigned to:</dt>
			<dd>{$controller_data.assigned|prettify}</dd>
		</dl>
	</div>
{/content_wrapper}