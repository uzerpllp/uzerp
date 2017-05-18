{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$model}
			<dl id="view_data_left">
				{assign var=fields value=$model->getDisplayFieldNames()}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{view_data attribute=$fieldname label=$tag}
				{/foreach}
				{view_data label='Assigned to' value="$count Ledger Accounts"}
			</dl>
		{/with}
	</div>
{/content_wrapper}