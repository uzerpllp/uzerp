{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
<div style="display: none">
	{with model=$permission}
		<form>
			<input type="hidden" name="PermissionData[type]" value="standard" />
			{input type='hidden' attribute='id'}
			{input type='hidden' attribute='type'}
			{input type='hidden' attribute='parent_id'}
			{input type='hidden' attribute='permission'}
			<ul>
				<li>
					<label>Module</label>
					<select class="module" name="PermissionData[module]" data-type="module">
						<option></option>
						{html_options options=$options.modules selected=$selected.module}
					</select>
				</li>
				<li>
					<label>Controller</label>
					<select class="controller nonone" name="PermissionData[controller]" data-type="controller">
						<option></option>
						{html_options options=$options.controllers selected=$selected.controller}
					</select>
				</li>
				<li>
					<label>Action</label>
					<select class="action nonone" name="PermissionData[action]" data-type="action">
						<option></option>
						{html_options options=$options.actions selected=$selected.action}
					</select>
				</li>
				{if $parent_name != ''}
					<li>
						<label>Parent</label>
						<span>{$parent_name}</span>
					</li>
				{/if}
				<li>
					<label>Type</label>
					<span class="type">Unknown</span>
				</li>
			</ul>
			<ul>
				<li>
					<label>Title</label>
					{input attribute='title' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Description</label>
					{input attribute='description' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Display in Menus</label>
					{input type='checkbox' attribute='display' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Display on Sidebar</label>
					{input type='checkbox' attribute='display_in_sidebar' tags='none' nolabel='true'}
				</li>
			</ul>
			<ul>
				<li>
					<label style="width: 300px">Extra:</label>
					<textarea name="PermissionData[extra]" style="width: 300px; float: left; clear: both" placeholder="key=value">{$parameter_string}</textarea>
				</li>
			</ul>
			<p>
				<button data-action="save">Save Standard Permission</button>
				<button data-action="cancel">Cancel</button>
			</p>
		</form>
	{/with}
	
	<script>
	
		$(document).ready(function() {
			update_type_label();
		});
		
	</script>
	
</div>