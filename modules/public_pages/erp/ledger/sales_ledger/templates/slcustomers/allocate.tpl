{content_wrapper}
	{form controller="slcustomers" action="save_allocation" _id=$controller_data.id}
		{data_table class="uz-grid-table"}
			{heading_row}
				{heading_cell field="customer"}
					Customer
				{/heading_cell}
				{heading_cell field="person"}
					Person
				{/heading_cell}
				{heading_cell field="our_reference"}
					Our Reference
				{/heading_cell}
				{heading_cell field="ext_reference"}
					Customer Reference
				{/heading_cell}
				{heading_cell field="transaction_type"}
					Transaction Type
				{/heading_cell}
				{heading_cell field="transaction_date"}
					Transaction Date
				{/heading_cell}
				{heading_cell field="gross_value" class='right'}
					Gross Value
				{/heading_cell}
				{heading_cell field="os_value" class='right'}
					OS Value
				{/heading_cell}
				{heading_cell field="currency"}
					Currency
				{/heading_cell}
				{heading_cell field="settlement_discount" class='right'}
					Settlement Discount
				{/heading_cell}
				{heading_cell class='right'}
					Allocation Amount
				{/heading_cell}
				{heading_cell }
					Discount?
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
					<td align=left>{$transaction->customer}</td>
					<td align=left>{$transaction->person}</td>
					<td>{$transaction->our_reference}</td>
					<td>{$transaction->ext_reference}</td>
					<td>{$transaction->getFormatted('transaction_type')}</td>
					<td>{$transaction->getFormatted('transaction_date')}</td>
					<td align=right>{$transaction->gross_value|string_format:"%.2f"}</td>
					<td align=right>{$transaction->os_value|string_format:"%.2f"}
						<input type="hidden" id='os_value{$rowid}' value={$transaction->os_value}>
					</td>
					<td align=center>{$transaction->currency}</td>
					<td align=right>
						{if $transaction->allow_discount_on_allocation=='t' && $transaction->transaction_type=='I'}
							{input type="text" model=$transaction attribute='settlement_discount' rowid=$rowid class="discount numeric" number=$transaction->id tags=none label=' '}
						{else}
							{$transaction->settlement_discount}
						{/if}
					</td>
					<td align=right>
						{input type="hidden" model=$transaction attribute="os_value_original" rowid=$rowid class="allocation numeric" number=$transaction->id tags=none label=' ' value=$transaction->os_value}
						{input type="hidden" model=$transaction attribute="os_value_copy" rowid=$rowid class="allocation numeric" number=$transaction->id tags=none label=' ' value=$transaction->os_value}
						{input type="text" model=$transaction attribute="os_value" rowid=$rowid class="allocation numeric" number=$transaction->id tags=none label=' '}
					</td>
					<td align=center>
						{if $transaction->allow_discount_on_allocation=='t' && $transaction->transaction_type=='I'}
							{input model=$transaction attribute="include_discount" rowid=$rowid type="checkbox" class="checkbox include_discount" number=$transaction->id tags=none label=' '}
							{input type="hidden" model=$transaction attribute="sl_discount_glaccount_id" rowid=$rowid number=$transaction->id}
							{input type="hidden" model=$transaction attribute="sl_discount_glcentre_id" rowid=$rowid number=$transaction->id}
							{input type="hidden" model=$transaction attribute="sl_discount_description" rowid=$rowid number=$transaction->id}
						{/if}
					</td>
					<td align=center>{input model=$transaction attribute="allocate" rowid=$rowid type="checkbox" class="checkbox allocate" number=$transaction->id tags=none label=' '}</td>
				</tr>
			{/foreach}
			<tr>
				<td align='right' colspan=5>
					Allocated total
				</td>
				<td align='right'>
					<input type='text' class="numeric" name="allocated_total" id="allocated_total" value={$allocated_total|string_format:"%.2f"}>
				</td>
				<td align='right' colspan=7>
				</td>
			</tr>
		{/data_table}
		{submit another='false'}
	{/form}
{/content_wrapper}