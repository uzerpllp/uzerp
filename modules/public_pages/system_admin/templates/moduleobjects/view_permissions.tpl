{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$ModuleObject}
			<dl id="view_data_left">
				{view_data attribute="permission" label='name'}
				{view_data attribute="title"}
				{view_data attribute="description"}
				{view_data attribute="location"}
				{view_data attribute="display" label='enabled'}
			</dl>
		{/with}
	</div>
	{form controller="moduleobjects" action="save_permissions"}
	  	{input type='hidden' attribute='module_id' value=$module_id}
		<table>
			<tr>
	        	<th align='left'>
					Permission
	    	    </th>
		        <th align='left'>
					Title
	        	</th>
		        <th align='left'>
					Description
	        	</th>
		        <th align='left'>
					Enabled
	        	</th>
			</tr>
			{foreach name=datagrid item=permission from=$controllers}
				{with model=$permission}
					<tr>
	            		 <td>
							{$permission->permission}
		        	     	<input type='hidden' name=PermissionCollection[id][] value="{$permission->id}">
		        	     	<input type='hidden' name=PermissionCollection[parent_id][] value="{$module_id}">
		        	     	<input type='hidden' name=PermissionCollection[type][] value='c'>
			             	<input type='hidden' name=PermissionCollection[permission][] value="{$permission->permission}">
			             	<input type='hidden' name=PermissionCollection[location][]  value="{$permission->location}">
	    		         </td>
			             <td>
							<input type='text' name=PermissionCollection[title][] value="{$permission->title}">
	        		    </td>
			             <td>
							<input type='text' name=PermissionCollection[description][] value="{$permission->description}">
	        		    </td>
		            	 <td>
		            	 	{if $permission->display}
								<input type='checkbox' name=PermissionCollection[display][] checked=true>
							{else}
								<input type='checkbox' name=PermissionCollection[display][]>
							{/if}
		        	    </td>
					</tr>
				{/with}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		</table>
		{submit}
	{/form}
{/content_wrapper}