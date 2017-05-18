{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper class="clearfix uz-grid"}
	<dl id="view_data_left">
		{form controller="pinvoicelines" action="save"}
			{with model=$pinvoice legend="Purchase Invoice Header"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='plmaster_id' }
				{include file='elements/auditfields.tpl' }
			{/with}
			{with model=$models.PInvoiceLine legend="Purchase Invoice Line Details"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='invoice_id' }
				{input type="hidden" attribute="status"}
				{include file='elements/auditfields.tpl' }
				{input type="hidden" attribute="line_number"}
				{if $model->line_number != '' }
					{view_data attribute="line_number"}
				{/if}
				{input type="text" attribute='description'}
				{input type="text" attribute='gr_note' label="goods received note"}
				{input type="text" attribute="net_value" class="price numeric"}
				{input type="text" attribute="tax_value" class="price numeric"}
				{select attribute="glaccount_id" options=$glaccount_options}
				{select attribute="glcentre_id" options=$glcentre_options}
				{select attribute="tax_rate_id" options=$taxrate_options nonone=true}
				{input type="text" attribute="gross_value" class="gross_value price numeric" readonly=true}
			{/with}
			{if !$dialog}
				{submit}
				{submit name="saveAnother" value="Save and Add Another"}
			{/if}
		{/form}
		{if !$dialog}
			{with model=$models.PInvoiceLine legend="Purchase Invoice Line Details"}
				{if $model->id!=''}
					{form id='delete_form' controller="pinvoicelines" action="delete"}
						{input type='hidden' attribute='id' }
						{input type='hidden' attribute='invoice_id' }
						{submit id='saveform' name='delete' value='Delete'}
					{/form}
				{/if}
			{/with}
			{include file='elements/cancelForm.tpl'}
		{/if}
	</dl>
{/content_wrapper}