{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper title='view '|cat:$SelectorObject->description|cat:' '|cat:$SelectorObject->name}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$SelectorObject}
				{foreach item=item key=key from=$parent}
					{view_data attribute=name value=$item.name label=$item.description link_to='"module":"'|cat:$module|cat:'","controller":"'|cat:$controller|cat:'","action":"view","id":"'|cat:$key|cat:'"'}
				{/foreach}
				{view_data attribute='name' label=$model->description}
			{/with}
		</dl>
	</div>
	{if $collection|count > 0}
		<div id="view_page" class="clearfix">
			<dl id="view_data_left">
				{view_section heading=$child_description}
				{/view_section}
			</dl>
		</div>
		{include file="elements/datatable.tpl"}
	{/if}
	<div id="view_data_bottom">
		<div id="view_page" class="clearfix">
			{if $selectorobjects|count > 0 }
				{view_section heading="Uses $component_count Components"}
					{data_table}
						{heading_row}
							{foreach key=name item=tag from=$headings}
								{heading_cell field=$name}
									{$tag}
								{/heading_cell}
							{/foreach}
						{/heading_row}
						{foreach name=datagrid item=model from=$selectorobjects}
							{grid_row}
								{foreach key=name item=tag from=$headings}
									{grid_cell model=$model field=$name no_escape=true}
										{$model->$name}
									{/grid_cell}
								{/foreach}
							{/grid_row}
						{/foreach}
					{/data_table}
					{paging}
				{/view_section}
			{/if}
		</div>
	</div>
{/content_wrapper}