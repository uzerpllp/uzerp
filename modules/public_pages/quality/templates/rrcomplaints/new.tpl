{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.18 $ *}
{content_wrapper}
	{form controller="Rrcomplaints" action="save"}
		{with model=$models.RRComplaint legend="Complaint Details"}
			{if $action=='edit'}	
				<h1 class="page_title">Complaint Number: {$model->type}{$model->complaint_number}</h1>
				<div class="clearfix"></div>
			{/if}
			{input type='hidden'  attribute='id'}
			<dl id="view_data_left">
				{view_section heading="complaint_details" expand="open"}
					{input type='date' attribute='date' class="compulsory" }
					{input type='text' attribute='order_number' class="compulsory"} 
					{input type='text' attribute='customer' class="compulsory"} 
					{select attribute='slmaster_id' label='Retailer' class="compulsory" options=$retailers} 
					{select attribute='stitem_id' label='Product' class="compulsory"}
					{input type='checkbox' attribute='product_complaint' }
				{/view_section}
				{view_section heading="complaint_code" expand="open"}
					{select attribute='complaint_code_id' label='Complaint Code' class="compulsory" }	
					{select attribute='supplementary_code_id' label='Supp Complaint Code'}
				{/view_section}
				{view_section heading="credit_details" expand="open"}
					{select attribute='currency_id' value=$default_currency label='Currency' class="compulsory" }
					{input type='text' attribute='credit_amount' class="compulsory"}
					{input type='text' attribute='credit_note_no' class="compulsory"}
					{input type='text' attribute='invoice_debit_no' label='Invoice / Debit No' class="compulsory"}
				{/view_section}
				{view_section heading="completion_details" expand="open"}
					{input type='date' attribute='date_complete' class="compulsory"}
					{select attribute='assignedto' label='Assigned To' class="compulsory"}
				{/view_section}
				{view_section heading="save_complaint" expand="open"}
					{submit id='saveform' name='save' value='Save and Reload'}
					{submit id='saveform' name='save' value='Save and Close'}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="problem_details" expand="open"}
					{textarea attribute='problem' label_position='above'}
					{textarea attribute='investigation' label_position='above'}
					{textarea attribute='outcome' label='Action' label_position='above'}
				{/view_section}
			</dl>
		{/with}
	{/form}
{/content_wrapper}