{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<div id="sales_order-soproductlines-sales_price_check">
	<form action="/?module=sales_orders&controller=sorderlines&action=getPriceInformation" method="POST">
		<dl id="view_data_left" style="width: 240px;">
			<p>
				{select model=$content.orderline attribute=so_price_type_id label='Price Type'}
			</p>
			<p>
				{select model=$content.orderline attribute=slmaster_id label='Customer'}
			</p>
			<p>
				{input model=$content.orderline attribute='product_search' label='product_search' value='None'}
			</p>
			<p>
				{select model=$content.orderline attribute=productline_id label='Select Product' nonone=true}
			</p>
		</dl>
		<dl id="view_data_right" style="width: 240px;">
			<ul>
				<li>&nbsp;</li>
				<li>
					<span id='SOProductline_currency'></span>
				</li>
				<li>&nbsp;</li>
				<li>
					<strong>Product Price (Excl VAT)</strong>
					<span id='SOProductline_product_price'></span>
				</li>
				<li>&nbsp;</li>
				<li>
					<strong>Customer Discount <strong id='SOProductline_discount_percent'></strong>%</strong>
					<span id='SOProductline_discount_value'></span>
				</li>
				<li>&nbsp;</li>
				<li>
					<strong>Net Price</strong>
					<span id='SOProductline_price'></span>
				</li>
				<li>&nbsp;</li>
				<li>
					<strong>VAT</strong>
					<span id='SOProductline_vat'></span>
				</li>
				<li>&nbsp;</li>
				<li>
					<strong>VAT Inclusive Price</strong>
					<strong>
						<span id='SOProductline_gross'></span>
					</strong>
				</li>
			</ul>
		</dl>
	</form>
</div>