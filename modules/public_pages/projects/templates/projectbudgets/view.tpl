{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$model legend="Project Budget Details"}
			{view_data attribute='project'}
			{view_data attribute='task'}
			{view_data attribute='budget_item_type'}
			{view_data attribute='budget_item'}
			{view_data attribute='description'}
			{view_data attribute='quantity'}
			{view_data attribute='uom_name'}
		<dl id="view_data_bottom">
		{view_section heading="Costs/Charges"}
			{data_table}
				<tr>
					<th>
						Description
					</th>
					<th class="right">
						Cost
					</th>
					<th class="right">
						Revenue
					</th>
				</tr>
				<tr>
					<td>
						Setup
					</td>
					<td align="right">
						{$model->setup_cost}
					</td>
					<td align="right">
						{$model->setup_charge}
					</td>
				</tr>
				<tr>
					<td>
						Rate
					</td>
					<td align="right">
						{$model->cost_rate}
					</td>
					<td align="right">
						{$model->charge_rate}
					</td>
				</tr>
				<tr>
					<td>
						Total Rate
					</td>
					<td align="right">
						{$model->total_cost_rate}
					</td>
					<td align="right">
						{$model->total_charge_rate}
					</td>
				</tr>
				<tr>
					<td>
						Totals (Total Rate+Setup)
					</td>
					{assign var=total_cost value=$model->setup_cost+$model->total_cost_rate}
					<td align="right">
						{$total_cost}
					</td>
					{assign var=total_charge value=$model->setup_charge+$model->total_charge_rate}
					<td align="right">
						{$total_charge}
					</td>
				</tr>
			{/data_table}
		{/view_section}
		</dl>
		{/with}
	</div>
{/content_wrapper}