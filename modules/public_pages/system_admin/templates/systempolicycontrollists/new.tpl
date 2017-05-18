{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="systempolicycontrollists" action="save"}
		{with model=$SystemPolicyControlList legend="System Policy Control List"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='object_policies_id' force=true}
			{select attribute='access_lists_id' force=true}
			{select attribute='allowed' options=$allowed}
			{select attribute='type' options=$type}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' action="cancel"}
{/content_wrapper}