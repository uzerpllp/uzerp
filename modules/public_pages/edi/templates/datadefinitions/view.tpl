{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.11 $ *}
	<div id="view_page" class="clearfix">
		{with model=$datadefinition}
			<dl class='float-left'>
				{view_data attribute="external_system" link_to='"controller":"externalsystems", "module":"edi", "action":"view", "id":"'|cat:$datadefinition->external_system_id|cat:'"'}
				{view_data attribute="name"}
				{view_data attribute="type"}
				{view_data attribute="description"}
				{view_data attribute="direction"}
				{view_data attribute='external_identifier_field'}
				{view_data attribute='process_model'}
				{view_data attribute='process_function'}
				{view_data attribute='implementation_class'}
				{view_data label="Awaiting $type" value=$edilogs->total_records}
				{view_data label="Errors Outstanding" value=$errors}
			</dl>
			<dl class='float-right'>
				{view_data attribute='field_separator' }
				{view_data attribute='text_delimiter' }
				{view_data attribute='abort_action' }
				{view_data attribute='duplicates_action' }
				{view_data attribute='file_prefix'}
				{view_data attribute='file_extension'}
				{view_data attribute='root_location' class="compulsory" label='Remote Target Location'}
				{view_data attribute='folder' label='Remote Target Folder'}
				{view_data attribute='remote_archive_folder' label='Remote Archive Folder'}
				{view_data attribute='local_archive_folder'}
				{view_data attribute='working_folder' label='Local Working Folder'}
			</dl>
		{/with}
	</div>
	{form controller="datadefinitions" action="process_files"}
		{with model=$datadefinition}
			{input type="hidden" attribute="id"}
		{/with}
		{paging}
		{data_table}
			<tr>
				<th>
					File
				</th>
				<th>
					Message
				</th>
				<th>
					Status
				</th>
				<th>
					Action
				</th>
				<th>
					Last Action Date
				</th>
			</tr>
			{foreach name=datagrid item=list from=$edilogs}
				<tr>
					<td>
						{assign var=id value=$list->id}
						{input model=$list type="hidden" attribute="filename" value=$list->name number=$id}
						{if $datadefinition->direction=='IN'}
							{link_to module=$module controller=$controller action='view_file' id=$datadefinition->id data=$list->external_id filename=$list->name implementation_class=$datadefinition->implementation_class value=$list->name}
							{assign var=data value=$list->external_id}
						{else}
							{link_to module=$module controller=$controller action='view_file' id=$datadefinition->id data=$list->internal_id filename=$list->name implementation_class=$datadefinition->implementation_class value=$list->name}
							{assign var=data value=$list->internal_id}
						{/if}
						{input model=$list type="hidden" attribute="data" number=$id value=$data}
					</td>
					<td>
						{$list->message|uzh}
					</td>
					<td>
						{$list->getFormatted('status')}
					</td>
					<td>
						{$list->getFormatted('action')}
					</td>
					<td>
						{$list->getFormatted('lastupdated')}
					</td>
				</tr>
			{foreachelse}
				<tr><td colspan="0">Nothing to {$type}</td></tr>
			{/foreach}
		{/data_table}
		{if $edilogs|count>0}
			{submit value="$type Now"}
		{/if}
	{/form}
{/content_wrapper}