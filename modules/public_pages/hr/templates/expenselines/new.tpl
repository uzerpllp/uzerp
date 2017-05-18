{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper class="clearfix uz-grid" }
	{form controller="expenselines" action="save" }
		{with model=$models.ExpenseLine legend="Expense Line Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='expenses_header_id' }
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='awaitingAuth' value=$awaitingAuth}
			{if $awaitingAuth}
				{input type='text' attribute='line_number' value=$line_number}
				{input type='text' attribute='item_description' }
				{input type='text' attribute='qty' }
				{input type='text' attribute='purchase_price' }
				{view_data attribute='currency'}
				{input type='hidden' attribute='currency_id' }
				{input type='hidden' attribute='rate' }
				{input type='text' attribute='net_value' }
				{input type='text' attribute='tax_value' }
				{select attribute='tax_rate_id'}
				{input type='text' attribute='gross_value'}
				{select attribute='glaccount_id' options=$accounts}
				{select attribute='glcentre_id' options=$centres}
			{else}
				{view_data attribute='line_number'}
				{view_data attribute='item_description' }
				{view_data attribute='qty' }
				{view_data attribute='purchase_price' }
				{view_data attribute='currency'}
				{input type='hidden' attribute='currency_id' }
				{input type='hidden' attribute='rate' }
				{input type='text' attribute='net_value' readonly='readonly' class='readonly'}
				{input type='text' attribute='tax_value' }
				{select attribute='tax_rate_id'}
				{input type='text' attribute='gross_value' readonly='readonly' class='readonly'}
				{view_data attribute='glaccount_id'}
				{view_data attribute='glcentre_id'}
			{/if}
		{/with}
		{if !$dialog}
			{submit}
			{submit name="saveAnother" value="Save and Add Another"}
		{/if}
	{/form}
	{if !$dialog}
		{with model=$models.ExpenseLine legend="Expense Line Details"}
			{if $model->id!=''}
				{form id='delete_form' controller="expenselines" action="delete"}
					{input type='hidden' attribute='id' }
					{input type='hidden' attribute='expense_header_id' }
					{submit id='saveform' name='delete' value='Delete'}
				{/form}
			{/if}
		{/with}
		{include file='elements/cancelForm.tpl'}
	{/if}
{/content_wrapper}