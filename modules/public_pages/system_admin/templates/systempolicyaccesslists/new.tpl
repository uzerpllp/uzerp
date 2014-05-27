{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="systempolicyaccesslists" action="save"}
		{with model=$SystemPolicyAccessList legend="System Policy Access List Details"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='access_type' options=$access_types}
			{select attribute='access_object_id' options=$options}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
{/content_wrapper}