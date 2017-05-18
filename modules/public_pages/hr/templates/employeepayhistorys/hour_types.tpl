{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{data_table}
	{heading_row}
		{heading_cell}
			Payment Type
		{/heading_cell}
		{heading_cell}
			Hours Type
		{/heading_cell}
		{heading_cell class='right'}
			Units
		{/heading_cell}
		{heading_cell}
		{/heading_cell}
		{heading_cell}
			Rate
		{/heading_cell}
		{heading_cell class='right'}
			Total
		{/heading_cell}
		{heading_cell}
			Comment
		{/heading_cell}
	{/heading_row}
	{assign var=count value=0}
	{with model=$model}
	{foreach item=rate key=key from=$hour_types}
		{assign var=count value=$count+1}
		{assign var=total_id value=get_class($model)}
		<tr>
			<td>
				{$rate.payment_type}
				{input type='hidden' attribute='id' number=$count rowid=$count label='' tags=none value=$rate.id}
				{input type='hidden' attribute='allow_zero_units' number=$count rowid=$count label='' tags=none value=$rate.allow_zero_units}
			</td>
			<td>
				{$rate.hours_type}
			</td>
			<td class='align-right'>
				{if $rate.units_variable=='t'}
					{input type='text' attribute='pay_units' class='pay_units numeric' number=$count rowid=$count label='' tags=none value=$rate.default_units}
				{else}
					{$rate.default_units}
					{input type='hidden' attribute='pay_units' class='pay_units numeric' number=$count rowid=$count label='' tags=none value=$rate.default_units}
				{/if}
				{input type='hidden' attribute='hours_type_id' number=$count rowid=$count label='' tags=none value=$rate.hours_type_id}
				{input type='hidden' attribute='payment_type_id' number=$count rowid=$count label='' tags=none value=$rate.payment_type_id}
			</td>
			<td>
				{if $rate.pay_frequency!='' && $rate.rate_variable=='f'}
					{$rate.pay_frequency}
					{input type='hidden' attribute='pay_frequency_id' number=$count rowid=$count label='' tags=none value=$rate.pay_frequency_id}
				{else}
					{select attribute='pay_frequency_id' number=$count rowid=$count label='' tags=none value=$rate.pay_frequency_id}
				{/if}
			</td>
			<td>
				{if $rate.rate_variable=='t'}
					@ {input type='text' attribute='pay_rate' class='pay_rate numeric' number=$count rowid=$count label='' tags=none value=$rate.rate_value}
				{else}
					@ {$rate.rate_value}
					{input type='hidden' attribute='pay_rate' class='pay_rate numeric' number=$count rowid=$count label='' tags=none value=$rate.rate_value}
				{/if}
			</td>
			<td class='align-right'>
				<span id={$total_id}_pay_total{$count}>{($rate.default_units*$rate.rate_value)|number_format:2}</span>
			</td>
			<td>
				{input type='text' attribute='comment' class='pay_units' number=$count rowid=$count label='' tags=none value=$rate.comment}
			</td>
		</tr>
	{/foreach}
	{/with}
{/data_table}