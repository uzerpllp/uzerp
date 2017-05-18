{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl class='float-left'>
		<div id='folders' class='placeholder'>
			<dt data-type="files" class="heading expand open">
				Folders/File Types
			</dt>
			<div class="">
				{include file="./index_list.tpl"}
			</div>
			{form controller='attachments' action='new' _entity_id=$component_id _data_model='modulecomponent'}
				{submit value='Load File' tags=none}
			{/form}
		</div>
		</dl>
		<dl class='float-left'>
		<div id='files' class="placeholder">
			{view_section heading="Files"}
				{form controller="mydata" action='delete_files'}
					{include file="./file_list.tpl"}
					{submit value='Delete Selected'}
				{/form}
			{/view_section}
		</div>
		</dl>
	</div>
{/content_wrapper}