{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{if $tags==''}
	{assign var=tags value=none}
{/if}
{if $type==''}
	{assign var=type value='text'}
{/if}
{input model=$model type=$type attribute=$attribute value=$value tags=$tags name=$name}