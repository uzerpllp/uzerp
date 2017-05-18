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
				{input type="hidden" attribute="source" value="V"}
				{input type="hidden" attribute="type" value="J"}
				{input type="hidden" attribute="vat_type" value=$vat_type}
				{input type="text" attribute='net' number='value' value='0.00' label='Net Value '}
				{input type="text" attribute='vat' number='value' value='0.00' label='Vat Value '}
				{select attribute="glperiods_id" label="Post to Period"}
				{input type="text" attribute="reference"}
				{input type="text" attribute="comment"}
				{select attribute='glaccount_id' label='Account' force=true options=$gl_accounts}
				{select attribute='glcentre_id' label='Centre' options=$gl_centres}
				{submit}
			{/with}
		{/form}
		{include file="elements/cancelForm.tpl" action="index"}
	</div>
{/content_wrapper}