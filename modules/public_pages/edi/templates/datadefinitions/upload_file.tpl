{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="datadefinitions" action="save_file" enctype="multipart/form-data"}
		{with model=$datadef}
			{if $datadefs|count > 0}
				{select attribute='id' label='Import Type' options=$datadefs}
			{else}
				{input type='hidden' attribute='id'}
				{input type='hidden' attribute='name'}
			{/if}
			{input type='text' attribute='working_folder' label='Save in Folder'}
			{input type='text' attribute='local_name' value=$local_name label='Save as'}
		{/with}
		{input type='hidden' attribute='MAX_FILE_SIZE' value='10485760'}
		{input type='file' attribute='file' label='upload file' value=$upload_file}
		{submit value='Upload'}
		{include file='elements/saveAnother.tpl' value='Upload and Select Another'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}