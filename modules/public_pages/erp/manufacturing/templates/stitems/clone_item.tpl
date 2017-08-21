{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="stitems" action="save_clone"}
		{with model=$models.STItem legend="STItem Details"}
			<div id="view_page" class="clearfix">
				{input type='hidden' attribute='id' }
			    <dl id="view_data_left">
					{input type='text'  attribute='item_code' class="compulsory" }
					{input type='text'  attribute='description' }
					{if $model->structures->count()>0}
						<dt><label for="STItem_copy_structures">Copy Structures</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_structures" name="STItem[copy_structures]">
						</dd>
					{/if}
					{if $model->operations->count()>0}
						<dt><label for="STItem_copy_operations">Copy Operations</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_operations" name="STItem[copy_operations]">
						</dd>
					{/if}
					{if $model->outside_operations->count()>0}
						<dt><label for="STItem_copy_outside_operations">Copy Outside Operations</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_outside_operations" name="STItem[copy_outside_operations]">
						</dd>
					{/if}
					{if $model->uom_conversions->count()>0}
						<dt><label for="STItem_copy_uom_conversions">Copy UoM Conversions</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_uom_conversions" name="STItem[copy_uom_conversions]">
						</dd>
					{/if}
					{if $model->so_products->count()>0}
						<dt><label for="STItem_copy_so_products">Copy SO product</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_so_products" name="STItem[copy_so_products]">
						</dd>
					{/if}

					{if $model->so_products->count()>0}
						<dt><label for="STItem_copy_so_product_prices">Copy SO product lines</label>:</dt>
						<dd>
							<input type="checkbox" id="STItem_copy_so_product_prices" name="STItem[copy_so_product_prices]">
						</dd>
					{/if}

					{if $model->so_products->count()>0}
						{input type='date' attribute='pstart_date' label='Product Start Date' value=$smarty.now class="compulsory"}
					{/if}
		    	</dl>
			</div>
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}