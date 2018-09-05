{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* 	$Revision: 1.2 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="poproductlineheaders" action="save"}
			{with model=$models.POProductlineHeader legend="POProductline Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{if isset($product_group)}
					{view_data attribute='product_group' value=$product_group}
					{input type='hidden' attribute='prod_group_id'}
				{else}
					{select attribute='prod_group_id' options=$prod_groups}
				{/if}
				{if isset($stitem)}
					{view_data attribute='stitem' label='Stock Item' value=$stitem}
					{input type='hidden' attribute='stitem_id'}
				{else}
					{select attribute='stitem_id' label='Stock Item' nonone=true options=$stitems}
				{/if}
				{input type='text' attribute='description' value=$description}
				{if $self.action == 'edit'}
					{input type='checkbox' attribute="cascade_description_change" label="Update Product-line descriptions" value=false}
				{/if}
				{select attribute='stuom_id' options=$uoms label='UoM'}
				{select attribute='tax_rate_id' options=$tax_rates label='Tax Rate'}
				{input attribute='commodity_code'}
				{select attribute='glaccount_id' options=$gl_accounts label='GL Account'}
				{select attribute='glcentre_id' options=$gl_centres label='Cost Centre'}
				{input type='date' attribute='start_date' }
				{input type='date' attribute='end_date' }
			{/with}
			{submit}
			{submit id='saveform' name='saveadd' value='Save and Add Another'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	</dl>
{/content_wrapper}