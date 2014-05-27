{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$accessobject}
			<dl id="view_data_bottom">
				<table>
					{foreach item=tree from=$accessobject->tree}
						<tr>
							{foreach key=key item=item from=$tree}
								{if $key=='children'}
									{foreach key=childkey item=childitem from=$item}
										<td>
											{$childkey}={$childitem}
										</td>
									{/foreach}
								{else}
									<td>
										{$key}={$item}
									</td>
								{/if}
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</dl>
		{/with}
	</div>
{/content_wrapper}