{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$dataset legend="Dataset Details"}
			<dl id="view_data_left">
				{assign var='title' value=$model->getIdentifierValue()}
				{view_section heading="$title"}
					{assign var=fields value=$model->getDisplayFieldNames()}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{view_data attribute=$fieldname label=$tag}
					{/foreach}
				{/view_section}
			</dl>
		{/with}
		{data_table}
			{heading_row}
				{heading_cell model=$dataset_model}
						Field
				{/heading_cell}
				{heading_cell model=$dataset_model}
						Data Type
				{/heading_cell}
				{heading_cell model=$dataset_model}
						Length
				{/heading_cell}
				{heading_cell model=$dataset_model}
						Default Value
				{/heading_cell}
				{heading_cell model=$dataset_model}
						Mandatory
				{/heading_cell}
			{/heading_row}
			{foreach name=headings item=field key=fieldname from=$dataset_model->getFields()}
				{grid_row}
					{grid_cell field=$fieldname model=$dataset_model}
						{$field->tag}
					{/grid_cell}
					{grid_cell field=$fieldname model=$dataset_model}
						{$field->type}
					{/grid_cell}
					{grid_cell field=$fieldname model=$dataset_model}
						{$field->max_length}
					{/grid_cell}
					{grid_cell field=$fieldname model=$dataset_model}
						{$field->default_value}
					{/grid_cell}
					{grid_cell field=$fieldname model=$dataset_model no_escape=true}
						{if ($field->not_null)}
							Yes
						{else}
							No
						{/if}
					{/grid_cell}
				{/grid_row}
			{/foreach}
		{/data_table}
	</div>
{/content_wrapper}