{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{if $tags==''}
	{assign var=tags value=none}
{/if}
{select model=$model attribute=$attribute options=$options value=$value tags=$tags nonone=$nonone depends=$depends forceselect=$forceselect multiple=$multiple use_collection=$use_collection}