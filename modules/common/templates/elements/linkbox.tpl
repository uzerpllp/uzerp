{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<ul>
	{foreach item=data key=action from=$components.data}
		<li>{link_to data=$data value=$action|cat:' '|cat:$components.title}</li>
	{/foreach}
</ul>
