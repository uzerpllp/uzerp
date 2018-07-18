{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{foreach name=dashboard key=name item=uzlet from=$uzlets}
	{assign var='uzletid' value=$uzletid+1}
	<div id="uzlet_{$uzlet.id}" class="uzlet-type-{$uzlet.type}{if $uzlet.size == '2'} double_eglet{/if}">
		<div class="eglet">
			{view_section heading=$uzlet.title|prettify expand="open"}
				<img src="/assets/graphics/spinner.gif" />
				Loading....
			{/view_section}
		</div>
	</div>
	<script type="text/javascript">
		$('#uzlet_{$uzlet.id}').load( "/?module={$module}&controller=dashboard&action=refresheglet&uzletid={$uzlet.id}&uzlet={$name}&ajax" );
	</script>
{/foreach}
