{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{foreach name=datagrid key=key item=modulecomponent from=$data}
		{if is_array($modulecomponent)}
			<tr>
				<td colspan=2>
					{view_section heading=$key}
					{/view_section}
				</td>
			</tr>
			{assign var=view_components value=$smarty.template|substr:0:-19|cat:'view_components.tpl'}
			{include file=$view_components data=$modulecomponent count=$count}
		{else}
			<tr>
				<td>
					{link_to module="$module" controller="modulecomponents" action='view' id=$modulecomponent->id value=$modulecomponent->name}
					{with model=$modulecomponent}
						{input type='hidden' number="$key" attribute="id"}
						{input type='hidden' number="$key" attribute="name" value=$modulecomponent->name}
						{input type='hidden' number="$key" attribute="module_id" value=$modulecomponent->module_id}
						{input type='hidden' number="$key" attribute="type" value=$modulecomponent->type}
						{input type='hidden' number="$key" attribute="controller"}
						{input type='hidden' number="$key" attribute="location" value=$modulecomponent->location}
					{/with}
				</td>
	           	 <td>
					{with model=$modulecomponent}
						{input type='checkbox' number="$key" attribute="register" label=' ' tags='none'}
					{/with}
	       	    </td>
			</tr>
		{/if}
	{foreachelse}
		<tr><td colspan="0">No matching records found!</td></tr>
	{/foreach}
{/content_wrapper}