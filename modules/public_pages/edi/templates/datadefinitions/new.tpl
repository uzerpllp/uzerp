{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.9 $ *}
	<div id="{page_identifier}">
		{form controller="datadefinitions" action="save"}
			{with model=$models.DataDefinition legend="External Data Definitions"}
				<div id="view_page" class="clearfix">
					<dl class='float-left'>
						{input type='hidden'  attribute='id' }
						{include file='elements/auditfields.tpl' }
						{select attribute='external_system_id'}
						{input type='text' attribute='name' class="compulsory" }
						{select attribute='type' class="compulsory" label='File type' }
						{select attribute='transfer_type' class="compulsory" }
						{input type='textarea' attribute='description'}
						{select  attribute='direction' class="compulsory" }
						{input type='text' attribute='username'}
						{input type='password' attribute='password'}
						{select attribute='field_separator' }
						{select attribute='text_delimiter' }
						{select attribute='abort_action' }
						{select attribute='duplicates_action' }
					</dl>
					<dl class='float-right'>
						{input type='text' attribute='file_prefix'}
						{input type='text' attribute='file_extension'}
						{input type='text' attribute='external_identifier_field' label='external identifier field'}
						{input type='text' attribute='root_location' class="compulsory" label='Remote Target Location'}
						{input type='text' attribute='folder' label='Remote Target Folder'}
						{input type='text' attribute='remote_archive_folder' label='Remote Archive Folder'}
						{input type='text' attribute='local_archive_folder'}
						{input type='text' attribute='working_folder' label='Local Working Folder'}
						{input type='text' attribute='process_model'}
						{input type='text' attribute='process_function'}
						{input type='text' attribute='implementation_class' force=true}
					</dl>
				</div>
			{/with}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}