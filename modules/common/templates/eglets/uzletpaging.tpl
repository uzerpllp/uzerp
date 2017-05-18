{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{assign var='page_id' value=$smarty.now|rand:99999}
<div id='{$page_id}'>
	{assign var='currentpage' value=$content->page}
	{assign var='num_pages' value=$content->num_pages}
	{$uzlet->render()}
	{include file="{$smarty.const.STANDARD_EGLET_TPL_ROOT}paging.tpl"}
</div>