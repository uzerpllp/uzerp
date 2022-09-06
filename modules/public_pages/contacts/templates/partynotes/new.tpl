{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}	
{content_wrapper}
{form controller="partynotes" action="save"}
	<div class="form-columns">
		<div class="col single-form">
			<dl class="viewgrid">

					{with model=$models.PartyNote legend="PartyNote Details"}

							{input type='hidden'  attribute='id' }
							{include file='elements/auditfields.tpl' }
							{input type='text'  attribute='title' class="compulsory" }
							{select attribute='note_type' }
							{input type='hidden' attribute='party_id' }
							{textarea attribute='note' class="compulsory" }

					{/with}
			</dl>
		</div>
	</div>
	<dl class="form-actions single-form">
		{submit another=false}
	</dl>
{/form}
{/content_wrapper}