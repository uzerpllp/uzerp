{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 *
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper title=$page_title}
<div id="view_page" class="clearfix">
	{with model=$target}
		{foreach item=field from=$target_headings}
			{view_data attribute=$field}
		{/foreach}
	{/with}
</div>
<div id="view_page_bottom" class="clearfix">
	{advanced_search}
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				{foreach key=name item=tag from=$headings}
					<th>
						{$tag}
					</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$selectorobjects}
				<tr>
					{assign var=linedetail value=''}
					{foreach key=name item=tag from=$headings}
						<td>
							{$model->$name}
							{assign var=linedetail value=$linedetail|cat:','|cat:$name|cat:'='|cat:$model->$name}
						</td>
					{/foreach}
				</tr>
			{foreachelse}
				<tr>
					<td colspan="{$headings|count}">
						No matching records found!
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/content_wrapper}
