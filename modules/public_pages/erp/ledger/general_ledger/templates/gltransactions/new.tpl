{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.21 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="gltransactions" action="save"}
			{with model=$gltransaction_header legend="GL Transaction Journal Header"}
				{input type='hidden' attribute='id' }
			{/with}
			{with model=$gltransaction  legend="GL Transaction Journal Details"}
				{include file='elements/auditfields.tpl' }
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='docref' value=$gltransaction_header->docref}
				{if $gltransaction_header->type != 'Y'}
				{input type="text" attribute="reference" class="reference" value=$gltransaction_header->reference}
				{/if}
				{input type="text" attribute="comment" class="comment" value=$gltransaction_header->comment}
				{select attribute="glaccount_id" options=$accounts selected=$default_account force=true}
				{select attribute="glcentre_id" options=$centres selected=$default_centre force=true}
				{input type="text" attribute="debit" class="price debit numeric"}
				{input type="text" attribute="credit" class="price credit numeric"}
			{/with}
			{if !$dialog}
				{submit}
				{submit name="saveAnother" value="Save and Add Another"}
			{/if}
		{/form}
		{if !$dialog}
			{with model=$gltransaction  legend="GL Transaction Journal Details"}
				{if $model->id!=''}
					{form id='delete_form' controller="gltranactions" action="delete"}
						{input type='hidden' attribute='id' }
						{submit id='saveform' name='delete' value='Delete'}
					{/form}
				{/if}
			{/with}
			{include file='elements/cancelForm.tpl'}
		{/if}
	</dl>
{/content_wrapper}