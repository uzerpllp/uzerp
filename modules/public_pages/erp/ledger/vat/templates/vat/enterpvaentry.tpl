{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_data_left">
		{form controller=$self.controller action="savejournal"}
			{with model=$vat}
				{select attribute='invoice' label='Invoice' options=$invoices nonone=true}
				{input type="hidden" attribute="vat_type" value='PVA'}
				{input type="text" attribute='vat' number='value' value='0.00' label='Vat Value '}
				{select attribute="glperiods_id" label="Post to Period" options=$periods value=$current_period}
				{input type="text" attribute="reference"}
				{input type="text" attribute="comment"}
				
				{submit}
			{/with}
		{/form}
		{include file="elements/cancelForm.tpl" action="index"}
	</div>
{/content_wrapper}