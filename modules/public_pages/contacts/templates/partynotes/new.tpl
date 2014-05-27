{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}	
{content_wrapper}
	<dl id="view_data_left">
		{form controller="partynotes" action="save"}
			{with model=$models.PartyNote legend="PartyNote Details"}
				{view_section heading="Note Details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='text'  attribute='title' class="compulsory" }
					{select attribute='note_type' }
					{input type='hidden' attribute='party_id' }
					{textarea attribute='note' class="compulsory" }
				{/view_section}
			{/with}
			{submit}
		{/form}
		{include file="elements/cancelForm.tpl"}
	</dl>
{/content_wrapper}