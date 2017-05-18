{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data attribute="account"}
				{view_data attribute="cost_centre"}
				{view_data attribute="glperiod" label="Period"}
				{view_data attribute="transaction_date"}
				{if empty($linkmodule)}
					{view_data attribute="docref"}
				{else}
					{view_data attribute="docref" link_to='"module":"'|cat:$linkmodule|cat:'","controller":"'|cat:$linkcontroller|cat:'","action":"view","'|cat:$fklinkfield|cat:'":"'|cat:$transaction->docref|cat:'"'}
				{/if}
				{view_data attribute="source"}
				{view_data attribute="type"}
				{view_data attribute="comment"}
				{view_data attribute="twincurrency"}
				{view_data attribute="twin_rate"}
				{view_data attribute="twinvalue"}
				{if ($model->credit)>0 }
					{view_data attribute="credit"}
				{/if}
				{if ($model->debit)>0 }
					{view_data attribute="debit"}
				{/if}
				{view_data attribute="reference"}
			{/with}
		</dl>
	</div>
{/content_wrapper}