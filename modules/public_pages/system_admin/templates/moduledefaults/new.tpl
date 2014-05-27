{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<div id="{page_identifier}">
	{form controller="moduledefaults" action="save"}
		{with model=$models.ModuleDefault legend="Module Default Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='module_components_id' value=$module_components_id}
			{input type='hidden' attribute='field_name' value=$field_name}
			{view_data attribute='field_name' value=$field_name|prettify}
			{if $field->type=='select'}
				{select attribute='default_value' options=$options}
			{elseif ($field->type=='bool')}
				{input type='checkbox' attribute='default_value' }
			{else}
				{input type='text' attribute='default_value'}
			{/if}
			{input type='checkbox' attribute='enabled'}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
</div>