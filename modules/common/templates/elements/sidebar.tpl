{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{assign var="components" value=$sidebar->getComponents()}

{foreach name=components item=component key=name from=$components}
	<div id="sidebar_{$name}" class="sidebar_component">
		<div class="expand open title">
			<h3>{$name|prettify}</h3>
		</div>
		{sidebar->display type=$component.type name=$name}
	</div>
{/foreach}

