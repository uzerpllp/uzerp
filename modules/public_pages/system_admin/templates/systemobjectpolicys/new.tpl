{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{form controller="systemobjectpolicys" action="save"}
		{with model=$SystemObjectPolicy legend="System Data Policy Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='key_field' value=$key_field}
			{include file='elements/auditfields.tpl' }
			{input attribute='name'}
			{select attribute='module_components_id' options=$components force=true}
			{select attribute='fieldname' options=$fields}
			{select attribute='operator' options=$operators}
			<div id='input_value'>
				{$input_value}
			</div>
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
{/content_wrapper}