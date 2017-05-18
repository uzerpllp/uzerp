{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
<thead>
	{heading_row}
		{foreach name=headings item=heading key=fieldname from=$fields}
			{heading_cell field=$fieldname model=$collection->getModel()}
				{$heading}
			{/heading_cell}
		{/foreach}
		{if $allow_delete}
			<th>&nbsp;</th>
		{/if}
	{/heading_row}
</thead>