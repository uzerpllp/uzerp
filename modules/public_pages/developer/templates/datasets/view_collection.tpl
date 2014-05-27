{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$title}
	<input type="hidden" id="print_force_index" value="true" />
	{include file="elements/datatable.tpl" collection=$dataobjects _dataset_id=$dataset_id}
{/content_wrapper}