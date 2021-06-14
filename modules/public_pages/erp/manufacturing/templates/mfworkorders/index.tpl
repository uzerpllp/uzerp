{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.21 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller="mfworkorders" action="batchupdate"}
		{assign var=fields value=$mfworkorders->getHeadings()}
		{data_table}
			<thead>
				{heading_row}
					{foreach name=headings item=heading key=fieldname from=$fields}
						{heading_cell field=$fieldname model=$mfworkorders->getModel()}
							{$heading}
						{/heading_cell}
					{/foreach}
					{heading_cell}
						Mtl Issued?
					{/heading_cell}
					{heading_cell}
						Action
					{/heading_cell}
					{if $module_prefs['allow-wo-print'] !== 'D'}
					{heading_cell}
						Print
					{/heading_cell}
					{/if}
				{/heading_row}
			</thead>
			{foreach name=datagrid item=model from=$mfworkorders}
				{grid_row model=$model}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if ($model->isEnum($fieldname))}
								{$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/foreach}
					<td>
						{assign var=mtl_status value=$model->materialStatus()}
						{if $mtl_status['issued'] >= $mtl_status['required']}
							Yes
						{elseif ($mtl_status['issued'] < $mtl_status['required']) and $mtl_status['issued'] > 0}
						    In Part
						{/if}
					</td>
					<td>
						{if $model->status!='C'}
							<input type="checkbox" name="update[{$model->id}]" id="update{$model->id}" class="checkbox" />
							{if $model->status=='N'}
								<label for="update{$model->id}">Release Order</label>
								<input type='hidden' name='status[{$model->id}]' value='R' label='release'/>
							{elseif $model->status!='C'}
								<label for="update{$model->id}">Complete Order</label>
								<input type='hidden' name='status[{$model->id}]' value='C' />
							{/if}
						{/if}
					</td>
					{if (($model->status == 'N') && in_array($module_prefs['allow-wo-print'], ['A', 'N']))
						|| (($model->status == 'R') && in_array($module_prefs['allow-wo-print'], ['A', 'R']))}
						<td>
							<input title='Print documents to default printer' type="checkbox" name="print[{$model->id}]" id="print{$model->id}" class="checkbox" />
						</td>
					{/if}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{if $num_incomplete > 0}
			{if ($module_prefs['allow-wo-print'] !== 'D')}
				{submit value="Update/Print Selected" tags="none"}
			{else}
				{submit value="Update Selected" tags="none"}
			{/if}
		{/if}
	{/form}
	{paging}
	<div id="data_grid_footer" class="clearfix">
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}