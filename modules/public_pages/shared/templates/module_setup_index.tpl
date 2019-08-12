{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{if !empty($templateCode)}
			<dl class='float-left'>
				<div id=preferences class='placeholder'>
					{view_section heading='Preferences'}
						{form controller=$controller action='save_preferences'}
							{$templateCode}
							{submit}
						{/form}
					{/view_section}
				</div>
			</dl>
		{/if}
		{if $setup_options}
		<dl class='float-left'>
			<div id=data class='placeholder'>
				{view_section heading='Data'}
				{data_table}
					{heading_row}
						{heading_cell}
							Data Type
						{/heading_cell}
						{heading_cell}
							Current Count
						{/heading_cell}
					{/heading_row}
					{foreach name=setup_options item=option key=title from=$setup_options }
						<tr>
							<td>
								{link_to data=$option.link value=$title|prettify}
							</td>
							<td>
								{$option.count}
							</td>
						</tr>
					{foreachelse}
						<tr><td colspan="0">No matching records found!</td></tr>
					{/foreach}
				{/data_table}
				{/view_section}
			</div>
		</dl>
		{/if}
	</div>
{/content_wrapper}