{content_wrapper}
	{form controller="employees" action="save_allocation" _employee_id=$controller_data.employee_id}
	{data_table}
		{heading_row}
			{heading_cell field="employee"}
				Employee
			{/heading_cell}
			{heading_cell field="our_reference" }
				Our Reference
			{/heading_cell}
			{heading_cell field="ext_reference" }
				Reference
			{/heading_cell}
			{heading_cell field="transaction_type"}
				Transaction Type
			{/heading_cell}
			{heading_cell field="transaction_date"}
				Transaction Date
			{/heading_cell}
			{heading_cell field="gross_value"}
				Gross Value
			{/heading_cell}
			{heading_cell field="os_value" }
				OS Value
			{/heading_cell}
			{heading_cell field="currency"}
				Currency
			{/heading_cell}
			{heading_cell }
				Allocate?
			{/heading_cell}
		{/heading_row}
	{assign var=count value=0}
	{foreach name=transactions item=transaction from=$transactions}
		{assign var=count value=$count+1}
		{assign var=rowid value='row'|cat:$count}
		<tr rel="{$rowid}">
			<td align=leftt>{$transaction->employee}</td>
			<td align=right>{$transaction->our_reference}</td>
			<td align=right>{$transaction->ext_reference}</td>
			<td align=center>{$transaction->getFormatted('transaction_type')}</td>
			<td align=center>{$transaction->transaction_date}</td>
			<td align=right>{$transaction->gross_value|string_format:"%.2f"}</td>
			<td align=right>{$transaction->os_value|string_format:"%.2f"}
				<input type="hidden" id='os_value{$rowid}' value={$transaction->os_value}>
			</td>
			<td align=center>{$transaction->currency}</td>
			<td align=center><input id='allocate{$rowid}' class="checkbox" type="checkbox" name="transactions[{$transaction->id}]" /></td>
		</tr>
	{/foreach}
		<tr>
			<td align='right' colspan=4>
				Allocated total
			</td>
			<td align='right'>
				<input type='text' class="numeric" name="allocated_total" id="allocated_total" value="0">
			</td>
		</tr>
	</table>
	{submit another='false'}
	{/data_table}
	{/form}
{/content_wrapper}