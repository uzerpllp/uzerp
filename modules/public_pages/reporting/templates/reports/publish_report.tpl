{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{form controller="reports" action="save_report_roles" }
		{with model=$Report legend="Report Details"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{view_data attribute=description}
			{view_data attribute=owner}
		{/with}
		{with model=$hasreport}
			{select attribute='role_id' multiple=true value=$roles}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}