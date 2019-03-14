{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			<dt class="heading">Submission</dt>
			{view_data model=$model attribute="year"}
			{view_data model=$model attribute="tax_period"}
			{view_data model=$model attribute="finalised" label="Submitted"}
			{if $model->finalised == 't'}
			{view_data model=$model attribute="alteredby" label="Submitted By"}	
			{view_data model=$model attribute="period_key"}
			{view_data model=$model attribute="processing_date"}
			{view_data model=$model attribute="form_bundle"}
			{view_data model=$model attribute="charge_ref_number"}
			{view_data model=$model attribute="receipt_id_header" label="Receipt ID"}
			{view_data model=$model attribute="payment_indicator"}
			{/if}
		</dl>
		<dl id="view_data_right">
			<dt class="heading">VAT Values</dt>
            {view_data model=$model attribute="vat_due_sales"}
            {view_data model=$model attribute="vat_due_aquisitions"}
            {view_data model=$model attribute="total_vat_due"}
            {view_data model=$model attribute="vat_reclaimed_curr_period"}
            {view_data model=$model attribute="net_vat_due"}
            {view_data model=$model attribute="total_value_sales_ex_vat"}
            {view_data model=$model attribute="total_value_purchase_ex_vat"}
            {view_data model=$model attribute="total_value_goods_supplied_ex_vat"}
            {view_data model=$model attribute="total_aquisitions_ex_vat"}
		</dl>
	</div>
{/content_wrapper}