{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{if isset($eglets)}
	<script src="/assets/js/vendor/echarts/echarts.min.js"></script>
	<!-- dashboard specific resources -->
	{assign var='uzletid' value=0}
	{foreach name=dashboard key=name item=eglet from=$eglets}
		{assign var='uzletid' value=$uzletid+1}
		<div class="{$eglet->getClassName()}">
			{view_section heading=$name|prettify expand="open"}
				<div class="eglet_body" id="eglet_{$uzletid}">
					{$eglet->populate()}
					{$eglet->render()}
				</div>
			{/view_section}
		</div>
	{/foreach}

{/if}
{include file='dashboard_uzlets.tpl'}
