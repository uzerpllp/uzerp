{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{advanced_search}
<div id="view_page" class="clearfix">
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				{foreach item=heading from=$fields}
					<th>
						{$heading|prettify}
					</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$collection}
				<tr>
					{foreach name=target item=field from=$fields}
						<td>
							{if $smarty.foreach.target.iteration==1}
								{link_to controller=$controller module=$module action='used_by' target_id=$model->id value=$model->$field}
							{else}
								{$model->$field}
							{/if}
						</td>
					{/foreach}
				</tr>
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		</tbody>
	</table>
	{paging}
</div>