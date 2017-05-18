{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="reportdefinitions" action="save" }
		{with model=$models.ReportDefinition legend="Report Definition"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text' attribute='name'}
			{select attribute='report_type_id'}
			{input type='checkbox' attribute='user_defined'}
			{submit name="save" value="Save"}
			{textarea id="xsl" attribute='definition' class="code_editor" label_position='above'}
			{textarea id="xml" attribute='test_xml' class="code_editor" label_position='above'}
			{submit name="save" value="Save"}
		{/with}
	{/form}
{/content_wrapper}