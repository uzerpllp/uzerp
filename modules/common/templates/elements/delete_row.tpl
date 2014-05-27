{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{if empty($clickcontroller)}
	{assign var=clickcontroller value=$controller}
{/if}
{if empty($delete_action)}
	{assign var=delete_action value='delete'}
{/if}
{link_to _class="delete_row" module=$module controller=$clickcontroller action=$delete_action id=$model->id img="/themes/default/graphics/delete.png" alt="delete"}