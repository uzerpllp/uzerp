{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$report}
				<dt>Table/View Name</dt>
				<dd>{$report->tablename} &nbsp;</dd>
				<dt>Description</dt>
				<dd>{$report->description} &nbsp;</dd>
				<dt>Display fields</dt>
				<dd>{$display_fields} &nbsp;</dd>
				<dt>Break On Fields</dt>
				<dd>{$break_on_fields} &nbsp;</dd>
				<dt>Aggregate fields</dt>
				<dd>{$aggregate_fields} &nbsp;</dd>
				<dt>Search Fields</dt>
				<dd>{$search_fields} &nbsp;</dd>
				<dt>Filter Fields</dt>
				<dd>{$filter_fields} &nbsp;</dd>
			{/with}
		</dl>
		<dl id="view_data_bottom">
			{if !empty($roles)}
				{data_table}
					<tr>
						<th>
							Published Roles
						</th>
					</tr>
					{foreach item=role from=$roles}
						<tr>
							<td>
								{$role}
							</td>
						</tr>
					{/foreach}
				{/data_table}
			{/if}
		</dl>
	</div>
{/content_wrapper}