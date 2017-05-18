{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{if empty($value)}
	{assign var=value value='Save and Add Another'}
{/if}
{if empty($name)}
	{assign var=name value='saveAnother'}
{/if}
{if empty($id)}
	{assign var=id value='saveform'}
{/if}
{submit name='saveAnother' id=$id name=$name value=$value tags=$tags}