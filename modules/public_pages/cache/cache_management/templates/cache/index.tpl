{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<p>Only memcached values will appear below, <em><strong>not</strong></em> values from the file cache</p>
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Key</th>
			</tr>
		</thead>
		<tbody>
		{foreach name=datagrid key=id item=value from=$keys}
			<tr>
				<td>
					{link_to module=$module controller=$controller action='view' id=$value value=$value no_prettify=true}
				</td>
			</tr>
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
		</tbody>
	</table>
{/content_wrapper}