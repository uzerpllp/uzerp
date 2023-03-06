{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
<div style="display: none">
	{with model=$permission}
		<form>
			<input type="hidden" name="PermissionData[type]" value="group" />
			{input type='hidden' attribute='id'}
			{input type='hidden' attribute='type'}
			{input type='hidden' attribute='parent_id'}
			{input type='hidden' attribute='display_in_sidebar'}
			<ul>
				<li>
					<label>Permission</label>
					{input attribute='permission' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Title</label>
					{input attribute='title' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Description</label>
					{input attribute='description' tags='none' nolabel='true'}
				</li>
				<li>
					<label>Display</label>
					{input type='checkbox' attribute='display' tags='none' nolabel='true'}
				</li>
			</ul>
			<p>
				<button data-action="save">Save Group Permission</button>
				<button data-action="cancel">Cancel</button>
			</p>
		</form>
	{/with}
</div>