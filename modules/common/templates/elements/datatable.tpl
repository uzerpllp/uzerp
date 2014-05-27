{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.15 $ *}
{advanced_search}
{paging}
{assign var=templatemodel value=$collection->getModel()}
{assign var=fields value=$collection->getHeadings()}
{data_table}
	{include file='elements/datatable_heading.tpl'}
	{include file='elements/datatable_rows.tpl'}
{/data_table}
<div style="clear: both;">&nbsp;</div>