{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.19 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
	{form controller=$controller action="save_confirmsale"}
		<dl id="view_data_left">
		{with model=$SOrder}
			{view_data attribute="customer" label='Sales Account'}
			{view_data attribute="getPhone()" label='Phone' }
			{view_data attribute="getMobile()" label='Mobile' }
			{view_data attribute="order_date"}
				<dt>Note</dt>
				<dd class="inline" id="SOrder_notes">
						{include file="elements/datatable_inline.tpl"}
				</dd>
		{/with}
		</dl>
		<dl id="view_data_right">
		{view_section heading="Payment Details"}
			{with model=$SOrder}
				{view_data attribute="net_value" label='Net' value=$net_value}
				{if $settlement_discount>0}
					{view_data attribute="settlement_discount" value=$settlement_discount label='settlement_discount'}
					{input type="hidden" attribute="settlement_discount" value=$settlement_discount}
				{/if}
				{view_data attribute="tax_value" value=$tax_value label='VAT'}
				{view_data attribute="gross_value" value=$gross_value label='amount_due'}
				{input type="hidden" attribute="id"}
				{input type="hidden" attribute="tax_value" value=$tax_value}
				{input type="hidden" attribute="gross_value" value=$gross_value}
				{input type="text" attribute="ext_reference" class='compulsary'}
				<dt>
					<label for="payment_type_id">
						Payment Type
					</label>
				</dt>
				<dd>
					<select name="SOrder[payment_type_id]">
						{html_options options=$payment_types selected=$payment_type_default }
					</select>
				</dd>
			{/with}
		{/view_section}
		</dl>
		<div class="view_data_bottom">
			{input type='hidden' attribute='company_id' value=$SOrder->customerdetails->company_id}
			{view_section heading="Existing Customer"}
				{with model=$SOrder}
					{input type='hidden' attribute='party_id' value=$SOrder->customerdetails->companydetail->party_id}
					{input type='hidden' attribute='slmaster_id'}
					{select label='Select Name' attribute='person_id' class="compulsory" data=$people forceselect="true" depends="slmaster_id"}
					{view_data label=' or enter new details below'}
					{input type='text' attribute='firstname' label='First Name *' class="compulsory"}
					{input type='text' attribute='surname' label='Last Name *' class="compulsory"}
					{input type='text' attribute='phone' label='phone'}
					{input type='text' attribute='email' label='email *' class="compulsory"}
				{/with}
			{/view_section}
		<dl class="float-left">
			{view_section heading="Invoice Address"}
				{with model=$SOrder}
					{select attribute='inv_address_id' options=$inv_addresses label='Invoice Address'}
					{view_data model=$address label=' or enter new details below'}
					{input type='text' attribute='street1' rowid='_invoice' number='invoice' label='street1 *' class="compulsory"}
					{input type='text' attribute='street2' rowid='_invoice' number='invoice' label='street2'}
					{input type='text' attribute='street3' rowid='_invoice' number='invoice' label='street3'}
					{input type='text' attribute='town' rowid='_invoice' number='invoice' label='town *' class="compulsory"}
					{input type='text' attribute='county' rowid='_invoice' number='invoice' label='county' }
					{input type='text' attribute='postcode' rowid='_invoice' number='invoice' label='postcode *' class="compulsory"}
					{select attribute='countrycode' rowid='_invoice' number='invoice' label='country *' class="compulsory" options=$countries value=$country}
				{/with}
			{/view_section}
		</dl>
		<dl class="float-right">
			{view_section heading="Delivery Address"}
				{with model=$SOrder}
					{select nonone=true attribute='del_address_id' options=$del_addresses label='Delivery Address'}
					{view_data label=' or enter new details below'}
					{input type='text' attribute='street1' rowid='_delivery' number='delivery' label='street1 *' class="compulsory"}
					{input type='text' attribute='street2' rowid='_delivery' number='delivery' label='street2'}
					{input type='text' attribute='street3' rowid='_delivery' number='delivery' label='street3'}
					{input type='text' attribute='town' rowid='_delivery' number='delivery' label='town *' class="compulsory"}
					{input type='text' attribute='county' rowid='_delivery' number='delivery' label='county' }
					{input type='text' attribute='postcode' rowid='_delivery' number='delivery' label='postcode *' class="compulsory"}
					{select attribute='countrycode' rowid='_delivery' number='delivery' label='country *' class="compulsory" options=$countries value=$country}
				{/with}
			{/view_section}
		</dl>
		</div>
		{submit value='Confirm'}
	{/form}
	</div>
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}
