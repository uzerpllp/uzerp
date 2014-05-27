{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.20 $ *}
{content_wrapper}
	{form controller="mfworkorders" action="save"}
		{with model=$models.MFWorkorder legend="MFWorkorder Details"}
			<div id="view_page" class="clearfix">
			    <dl class="float-left" >
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{if $stitem}
						{view_data label='Stock Item' value=$stitem}
						{input type='hidden' attribute='stitem_id'}
					{else}
						{select attribute='stitem_id' label='Stock Item' options=$stitems }
					{/if}
					{select attribute='stuom_id' options=$uoms selected=$model->stuom_id label='UoM' nonone=true}
					{select attribute='data_sheet_id'}
					{input type='text'  attribute='order_qty' }
					{if $action=='edit'}
						{input type='text'  attribute='made_qty' readonly=true}
					{/if}
					{input type='date'  attribute='required_by' }
					{select attribute='project_id' value=$project}
					{input type='text'  attribute='text1' }
					{input type='text'  attribute='text2' }
					{input type='text'  attribute='text3' }
					{select  attribute='order_id' options=$sales_orders force=true}
					{input type='hidden'  attribute='current_orderline_id' value=$model->orderline_id}
					{select  attribute='orderline_id' options=$order_lines}
				</dl>
			    <dl class="float-left" >
					{select attribute='documentation' options=$documents multiple=true size="10" value=$selected_docs}
				</dl>
			</div>
		{/with}
			{submit}
			{submit value="Save and add another" name="saveAnother"}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}