{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$gltransactions_header}
			<dl class="float-left">
				{view_data attribute="docref"}
				{if $gltransactions_header->isStandardJournal()}
					{view_data attribute="glperiods_id" label="Period"}
					{view_data attribute="transaction_date"}
				{/if}
				{view_data attribute="accrual"}
				{if $gltransactions_header->isStandardJournal() && $gltransactions_header->accrual=='t'}
					{view_data attribute="accrual_period_id" label="Reverse in Period"}
				{/if}
				{view_data attribute="status"}
				{view_data attribute="type"}
				{view_data attribute="comment"}
				{view_data attribute="reference"}
			</dl>
			<dl class="float-right">
				{if $model->isUnposted()}
					{view_section heading='Journal Values'}
						{view_data name='debits' value=$debits label='debits'}
						{view_data name='credits' value=$credits label='credits'}
						{view_data name='difference' value=$difference label='difference'}
					{/view_section}
				{/if}
			</dl>
		{/with}
		<div id="view_data_bottom" class={$status}>
			{include file="elements/datatable.tpl" collection=$gltransactions}
		</div>
	</div>
{/content_wrapper}