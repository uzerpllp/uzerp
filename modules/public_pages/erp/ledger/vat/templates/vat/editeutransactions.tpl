{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="vat" action="savetransaction"}
		{with model=$transaction legend="Transaction Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' name='model_type' value=$model|get_class}
			{include file='elements/auditfields.tpl' }
			{view_data attribute=$company_field}
			{view_data attribute='stitem' label='Item' link_to='"controller":"stitems", "module":"manufacturing", "action":"view", "id":"'|cat:$model->stitem_id|cat:'"'}
			{view_data attribute=$date_field}
			{view_data attribute=$qty_field }
			{view_data attribute='uom_name' }
			{view_data label='Calculated Net Mass' value=$net_mass}
			{input type='text' attribute='net_mass' class="compulsory" label="$qty_field Net Mass"}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl' }
{/content_wrapper}