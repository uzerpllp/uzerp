{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper title=$title}
	{form controller=$controller action="save" enctype="multipart/form-data"}
		{input type='hidden' attribute='MAX_FILE_SIZE' value='10485760'}
		{input type='hidden' attribute='entity_id' value=$entity_id}
		{input type='hidden' attribute='data_model' value=$data_model}
		{if !is_null($file->name)}
			{input type='hidden' attribute='REPLACING' value=$file->name}
			{view_section heading="Current"}
				<dt>Name</dt><dd>{$file->name}</dd>
				{view_data model=$file attribute='revision'}
			{/view_section}
			{view_section heading="Replace with"}
			{/view_section}
		{/if}
		{input type='file' attribute='file' label='upload file'}
		{input type='text' attribute='revision' label='Version (Blank to auto-increment)' value=$file->revision+1}
		{textarea attribute='note' label='Note' value=$file->note}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}