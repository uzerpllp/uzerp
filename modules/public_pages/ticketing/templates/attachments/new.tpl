{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="attachments" action="save" enctype="multipart/form-data"}
		{input type='hidden' attribute='MAX_FILE_SIZE' value='10485760'}
		{input type='hidden' attribute='ticket_id' value=$ticket_id}
		{input type='file' attribute='file' label='upload file'}
		{submit another='false'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}