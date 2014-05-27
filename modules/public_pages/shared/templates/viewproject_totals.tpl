{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{data_table}
	<thead>
		<tr>
			<th align=left>
				Budget Type
			</th>
			<th class=right>
				Setup Costs
			</th>
			<th class=right>
				Setup Charges
			</th>
			<th class=right>
				Total Rate Costs
			</th>
			<th class=right>
				Total Rate Charges
			</th>
			<th class=right>
				Total Budget Costs
			</th>
			<th class=right>
				Total Budget Charges
			</th>
			<th class=right>
				Hours to Date
			</th>
			<th class=right>
				Total Costs
			</th>
			<th class=right>
				Total Charges
			</th>
			<th class=right>
				Invoiced
			</th>
		</tr>
	</thead>
	{foreach item=budget key=budget_type from=$budget_totals}
		<tr>
			<td>
				{$budget_type}
			</td>
			<td align=right>
				{$budget.setup_cost}
			</td>
			<td align=right>
				{$budget.setup_charge}
			</td>
			<td align=right>
				{$budget.total_cost_rate}
			</td>
			<td align=right>
				{$budget.total_charge_rate}
			</td>
			<td align=right>
				{$budget.setup_cost+$budget.total_cost_rate}
			</td>
			<td align=right>
				{$budget.setup_charge+$budget.total_charge_rate}
			</td>
			<td align=right>
				{$budget.total_hours}
			</td>
			<td align=right>
				{$budget.total_costs}
			</td>
			<td align=right>
				{$budget.total_charges}
			</td>
			<td align=right>
				{$budget.total_invoiced}
			</td>
		</tr>
		{assign var=total_setup_cost value="{$total_setup_cost + $budget.setup_cost}"}
		{assign var=total_cost_rate value="{$total_cost_rate + $budget.total_cost_rate}"}
		{assign var=total_setup_charge value="{$total_setup_charge + $budget.setup_charge}"}
		{assign var=total_charge_rate value="{$total_charge_rate + $budget.total_charge_rate}"}
		{assign var=total_hours value="{$total_hours + $budget.total_hours}"}
		{assign var=total_costs value="{$total_costs + $budget.total_costs}"}
		{assign var=total_charges value="{$total_charges + $budget.total_charges}"}
		{assign var=total_invoiced value="{$total_invoiced + $budget.total_invoiced}"}
	{foreachelse}
		No budgets found!
	{/foreach}
	<tr>
		<td class="sub_total">
			Totals
		</td>
		<td class="sub_total" align=right>
			{$total_setup_cost}
		</td>
		<td class="sub_total" align=right>
			{$total_setup_charge}
		</td>
		<td class="sub_total" align=right>
			{$total_cost_rate}
		</td>
		<td class="sub_total" align=right>
			{$total_charge_rate}
		</td>
		<td class="sub_total" align=right>
			{$total_setup_cost+$total_cost_rate}
		</td>
		<td class="sub_total" align=right>
			{$total_setup_charge+$total_charge_rate}
		</td>
		<td class="sub_total" align=right>
			{$total_hours}
		</td>
		<td class="sub_total" align=right>
			{$total_costs}
		</td>
		<td class="sub_total" align=right>
			{$total_charges}
		</td>
		<td class="sub_total" align=right>
			{$total_invoiced}
		</td>
	</tr>
{/data_table}