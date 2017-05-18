{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{* {include file="elements/datatable.tpl" collection=$holidayrequests} *}
	{advanced_search}
	<div id="calendar"></div>
	<div id="add_event" style="display: none;" >
		<input type="hidden" id="event_status" />
	</div>
	<div id="legend" class="sidebar_component" style="display: none;" >
		<div>
			<h3>Legend</h3>
		</div>
		<ul>
			{foreach from=$legend key=k item=v}
				<li class="{$v}">{$k}</li>
			{/foreach}
		</ul>
	</div>
{/content_wrapper}