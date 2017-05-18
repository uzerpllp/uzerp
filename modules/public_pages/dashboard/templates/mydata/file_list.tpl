{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

{foreach item=item key=key from=$mydata}

	{if $key=='directory'}
	
		{foreach item=directory key=dirname from=$item}
			<div id={$dirname} style='display:none;' class="placeholder">
				{view_section heading="$dirname"}
					{include file="./file_list.tpl" mydata=$directory parent_id=$dirname class_name=$class_name}
				{/view_section}
			</div>
		{/foreach}
		
	{else}
		<div id="view_data_bottom">
			<table class="datagrid">
				<thead>
					<tr>
						<th>Name</th>
						<th>Size</th>
						<th>Last Modified</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=file from=$item}
						<tr>
							<td>{link_to link=$file.link value=$file.name _target="_blank"}</td>
							<td>{$file.size}</td>
							<td>{$file.mtime}</td>
							<td>
								{assign var=id value=$file.delete.id}
								{input type='checkbox' name="file[$id][delete_file]" label='' tags=none}</td>
								{input type='hidden' name="file[$id][name]" value=$file.name}</td>
								{input type='hidden' name="file[$id][type]" value=$file.type}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	{/if}
{foreachelse}
	No files	
{/foreach}