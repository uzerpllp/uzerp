{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<div id="uzlet-{$module}-module_documents">
	{* {include file='./egletpaging.tpl' includetemplate='list_uzlet.tpl' clickcontroller='attachments' clickaction='download' linkfield='file_id'} *}
	<h1>{$module|prettify}</h1>
	<table class='datagrid'>
		<tr>
			<th width=30% align=center>
				Document
			</th>
			<th width=5% align=right>
				Version
			</th>
			<th width=45% align=left>
				Note
			</th>
			<th align="center" colspan=2>
			</th>
		</tr>
		{foreach item=document key=id from=$content}
			<tr>
				<td width=10 align=left>
					{link_to module=$module controller="attachments" action="view_file" file_id=$document->file_id value=$document->document _target="_blank"}
				</td>
				<td align=right>
					{$document->revision}
				</td>
				<td>
					{$document->note}
				</td>
				<td width=10 align=center>
					{link_to module=$module controller="attachments" action="edit" id=$document->id img="/themes/default/graphics/position.png" alt="Replace" _class="delete_row"}
				</td>
				<td width=10 align=center>
					{link_to module=$module controller="attachments" action="delete" id=$document->id img="/themes/default/graphics/delete.png" alt="delete" _class="delete_row"}
				</td>
			</tr>
		{/foreach}
	</table>
	{if $content->can_upload}
		<form action="/?module={$module}&controller=attachments&action=new&data_model=moduleobject" method="POST">
			{submit value="New Document"}
		</form>
	{/if}
</div>