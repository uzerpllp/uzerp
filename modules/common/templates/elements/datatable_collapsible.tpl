{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.16 $ *}
{advanced_search}
{paging}
{assign var=templatemodel value=$collection->getModel()}
{assign var=fields value=$collection->getHeadings()}
{if $collection->num_records > 0}
	{data_table}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$collection->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
				{if $data_table_actions}
					<th>&nbsp;</th>
				{/if}
			</tr>
		</thead>
	{*	{datatable_body collection=$collection} *}
		{foreach name=datagrid item=model from=$collection}
			{assign var=break value=''}
	{* Check for a break level *}
			{foreach name=gridrow item=tag key=fieldname from=$fields}
				{if $break=='' && isset($measure_fields.$fieldname)}
					{if $model->$fieldname==''}
						{assign var=model_value value='None'}
					{else}
						{assign var=model_value value=$model->$fieldname}
					{/if}
					{if $measure_fields.$fieldname<>'' && $measure_fields.$fieldname<>$model_value}
						{assign var=break value=$fieldname}
					{/if}
				{/if}
			{/foreach}
			{if $break<>''}
	{* Break found so output break totals *}
				{assign var=previous_measure value=''}
				{foreach item=tag key=measure_name from=$reverse_measure_fields}
	{* Roll Up totals from lower levels *}
					{if $previous_measure<>''}
						{foreach item=tag key=aggregate_name from=$aggregate_fields}
							{assign var=key value=$aggregate_name|cat:$previous_measure}
							{assign var=previous_total value=$total_fields.$key}
							{array name=total_fields key=$key value=0}
							{assign var=key value=$aggregate_name|cat:$measure_name}
							{assign var=new_total value="{$total_fields.$key + $previous_total}"}
							{array name=total_fields key=$key value=$new_total}
						{/foreach}
					{/if}
					{* output break on total row *}
					{if $break<>'' && !isset($aggregate_count) }
						{grid_row model=$model}
							{assign var=class value=''}
							{foreach name=gridrow item=tag key=fieldname from=$fields}
								{if $measure_name==$fieldname}
									{assign var=class value='sub_total'}
								{/if}
								{grid_cell field=$fieldname model=$model collection=$collection class=$class no_escape=true}
									{if $measure_name==$fieldname}
										{if $measure_fields.$fieldname=='None'}
											Total
										{else}
											Total {$measure_fields.$fieldname}
										{/if}
									{elseif isset($aggregate_fields.$fieldname)}
										{assign var=key value=$fieldname|cat:$measure_name}
										{if !empty($field_formatting[$fieldname]) }
											{number_format number=$total_fields.$key options=$field_formatting[$fieldname] }
										{else}
									    	{$total_fields.$key}
										{/if}
									{/if}
								{/grid_cell}
							{/foreach}
						{/grid_row}
						{assign var=previous_measure value=$measure_name}
					{else}
						{assign var=previous_measure value=''}
					{/if}
					{if $break==$measure_name}
					{* At the break level so stop here *} 
						{assign var=break value=''}
					{/if}
				{/foreach}
			{/if}
	{* Now output the detail line *}
			{grid_row model=$model}
				{assign var=break value=false}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if isset($measure_fields.$fieldname)}
						{* measure_field will be the lowest level measure field *}
						{assign var=measure_field value=$fieldname}
					{else}
	{* Add aggregate value to lowest break level total *}
						{if isset($aggregate_fields.$fieldname)}
							{assign var=key value=$fieldname|cat:$measure_field}
							{assign var=new_total value="{$total_fields.$key + $model->$fieldname}"}
							{array name=total_fields key=$key value=$new_total}
						{/if}
					{/if}
					{if $measure_fields.$fieldname<>$model->$fieldname}
						{assign var=break value=true}
					{/if}
					{if isset($enablelink.$fieldname)}
						{assign var=cellnum value=1}
					{else}
						{assign var=cellnum value=0}
					{/if}
					{grid_cell cell_num=$cellnum field=$fieldname model=$model collection=$collection no_escape=true}
						{if !isset($measure_fields.$fieldname) || $break}
						{* Print the field value if it is not
							a break field or break has occurred at this or a higher level *}
							{if ($model->isEnum($fieldname))}
					    		{$model->getFormatted($fieldname)}
							{else}
								{if !empty($field_formatting[$fieldname]) }
									{number_format number=$model->getFormatted($fieldname) options=$field_formatting[$fieldname] }
								{else}
							    	{$model->getFormatted($fieldname)}
								{/if}
							{/if}
						{/if}
					{/grid_cell}
					{if isset($measure_fields.$fieldname)}
						{if $model->$fieldname==''}
							{array name=measure_fields key=$fieldname value='None'}
						{else}
							{array name=measure_fields key=$fieldname value=$model->$fieldname}
						{/if}
					{/if}
				{/foreach}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{* Now force break on report and output final totals *}
		{assign var=previous_measure value=''}
		{if !isset($aggregate_count) }
			{foreach item=tag key=measure_name from=$reverse_measure_fields}
		{* Roll Up totals from lower levels *}
				{if $previous_measure<>''}
					{foreach item=tag key=aggregate_name from=$aggregate_fields}
						{assign var=key value=$aggregate_name|cat:$previous_measure}
						{assign var=prvious_total value=$total_fields.$key}
						{assign var=key value=$aggregate_name|cat:$measure_name}
						{assign var=new_total value="{$total_fields.$key + $prvious_total}"}
						{array name=total_fields key=$key value=$new_total}
					{/foreach}
				{/if}
				{grid_row model=$model}
					{assign var=class value=''}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{if $measure_name==$fieldname || $measure_name=='report'}
							{assign var=class value='sub_total'}
						{/if}
						{grid_cell field=$fieldname model=$model collection=$collection class=$class no_escape=true}
							{if $measure_name=='report' && $smarty.foreach.gridrow.iteration==1}
								{if ($num_pages>1)}
									Page Total
								{else}
									Report Total
								{/if}
							{elseif $measure_name==$fieldname}
								Total {$measure_fields.$fieldname}
							{elseif isset($aggregate_fields.$fieldname)}
								{assign var=key value=$fieldname|cat:$measure_name}
								{if !empty($field_formatting[$fieldname]) }
									{number_format number=$total_fields.$key options=$field_formatting[$fieldname] }
								{else}
								   	{$total_fields.$key}
								{/if}
							{/if}
						{/grid_cell}
					{/foreach}
				{/grid_row}
			{assign var=previous_measure value=$measure_name}
			{/foreach}
		{/if}
	{/data_table}
{else}
	<p>No matching records found!</p>
{/if}
{paging}
<div style="clear: both;">&nbsp;</div>