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
			{assign var=count value=0}
			{foreach name=datagrid item=permission from=$controllers}
				{assign var=count value=$count+1}
				{with model=$permission}
					<tr>
	            		 <td>
							{$permission->permission}
		        	     	{input type='hidden' attribute='id' value=$permission->id number=$count}
		        	     	{input type='hidden' attribute='parent_id' value=$module_id number=$count}
		        	     	{input type='hidden' attribute='type' value='c' number=$count}
			             	{input type='hidden' attribute='permission'  value=$permission->permission number=$count}
			             	{input type='hidden' attribute='location'  value=$permission->location number=$count}
	    		         </td>
			             <td>
							{input type='text' attribute='title' tags='none' label=' ' value=$permission->title number=$count}
	        		    </td>
			             <td>
							{input type='text' attribute='description' tags='none' label=' ' number=$count}
	        		    </td>
		            	 <td>
							{input type='checkbox' attribute='display' tags='none' label=' ' number=$count}
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