{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: $ *}
{content_wrapper}
	{form controller="uzlets" action="save" }
		{with model=$models.Uzlet legend="uzLet"}
			{view_section heading="uzLet"}
				{input type='hidden' attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='text' attribute='name'}
				{input type='text' attribute='title'}
				{input type='text' attribute='uses'}
				{input type='checkbox' attribute='preset' label='default'}
				{input type='checkbox' attribute='enabled'}
				{input type='checkbox' attribute='dashboard'}
			{/view_section}
		{/with}
		{with model=$models.UzletModuleCollection}
			{view_section heading="Modules"}
				<dt><label for="uzlet_modules">Modules</label>:</dt><dd>
					<select id="uzlet_modules" name="UzletModuleCollection[module_id][]" multiple>
						{html_options options=$uzlet_modules selected=$selected_uzlet_modules}
					</select>
				</dd>
			{/view_section}
		{/with}
		{with model=$models.UzletCallCollection}
			{view_section heading="Calls"}
				{textarea attribute='UzletCallCollection[Call]' label='Calls (in <span class="check_json">JSON</span> format)' value=$uzlet_calls}
			{/view_section}
		{/with}
		{submit name="save" value="Save"}
	{/form}
{/content_wrapper}