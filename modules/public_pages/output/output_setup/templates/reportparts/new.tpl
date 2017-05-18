{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: $ *}
{content_wrapper}
	{form controller="reportparts" action="save"}
		{with model=$models.ReportPart legend="Report Part"}
			{input type='hidden' attribute='id'}
			{include file='elements/auditfields.tpl'}
			{input type='text' attribute='name'}
			{textarea attribute='value' class="code_editor" label_position='above'}
			{submit name="save" value="Save"}
		{/with}
	{/form}
{/content_wrapper}