{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="sttypecodes" action="save"}
		{with model=$models.STTypecode legend="STTypecode Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='type_code' class="compulsory" }
			{input type='text'  attribute='description' }
			{select attribute='backflush_action_id' options=$backflush_actions label='Backflush Action'}
			{select attribute='complete_action_id' options=$complete_actions label='Complete Action'}
			{select attribute='issue_action_id' options=$issue_actions label='Issue Action'}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}