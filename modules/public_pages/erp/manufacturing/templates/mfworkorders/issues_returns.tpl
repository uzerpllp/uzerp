{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{$sub_title}
	{form controller="mfworkorders" action="save_transactions"}
		{input type='hidden' attribute='id' value=$id}
		{input type='hidden' attribute='type' value=$type}
		{with model=$models.STTransaction legend="STTransaction Details"}
			{input type='hidden' attribute='process_name' value=$process_name}
			{input type='hidden' attribute='process_id' value=$process_id}
			{input type='hidden' attribute='type_text' value=$type_text}
			{input type='hidden' attribute='usercompanyid' }
			<div id="view_page" class="clearfix">
			    <dl class="float-left" >
					{view_data model=$models.MFWorkorder attribute='stitem' label='Stock Item'}
					{view_data model=$models.MFWorkorder attribute='wo_number'}
					{select attribute='stitem_id' options=$structure_items label=$type_text|cat:' Stock Item'}
					{select attribute='whaction_id' options=$actions nonone='true' label='Action'}
					{select attribute='from_whlocation_id' options=$from_locations value=$from_whlocation_id nonone=true label='From Location'}
					{select attribute='from_whbin_id' options=$from_bins nonone=true label='Bin'}
					{select attribute='to_whlocation_id' options=$to_locations nonone=true label='To Location'}
					{select attribute='to_whbin_id' options=$to_bins nonone=true label='Bin'}
				</dl>
				<dl class='float-right'>
					{view_data attribute='uom_name' value="$uom_name" label='Unit of Measure'}
					{view_data model=$models.MFWorkorder attribute='order_qty'}
					{view_data attribute='balance' value="$balance" label='Available Balance is'}
					{view_data attribute='issued_qty' value="$issued_qty" label='issued_qty'}
					{view_data attribute='used_qty' value="$used_qty" label='used_qty'}
					{input type='text' attribute='qty' label=$type_text|cat:' qty' value=$required_qty}
					{input type='text' attribute='remarks'}
				</dl>
			</div>
		{/with}
		{submit}
		{submit value="Save and add another" name="saveAnother"}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id='STTransaction_issues_list'>
		{include file="./wo_issues_list.tpl"}
	</div>
	<script type="text/javascript" language="JavaScript">
		$('#STTransaction_stitem_id').focus();
	</script>
{/content_wrapper}