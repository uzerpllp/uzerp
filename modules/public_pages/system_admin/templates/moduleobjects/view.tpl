{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.14 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="moduleobjects" action="save_permissions"}
			{with model=$ModuleObject}
	  			{input type='hidden' attribute='id'}
				{view_data attribute="name" value=$model->name}
				{view_data attribute="description" value=$model->description}
				{view_data attribute="location"}
				{view_data attribute="registered"}
				{view_data attribute="enabled"}
				{view_data attribute="help_link"}
			{/with}
			<div id="view_data_left">
				{view_section heading='Components'}
					<dt>
						<label for="tree_div"></label>
					</dt>
					<dd>
						<ul id="permission_tree" class="permissions collapsible_tree">
							{with model=$models.ModuleObject legend="Module Details"}
								{include file=$permissions_tree collection=$components name=components class_name=permission}
							{/with}
						</ul>
					</dd>
				{/view_section}
			</div>
			<div id="view_data_bottom">
				{submit}
			</div>
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
{/content_wrapper}