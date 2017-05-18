{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.12 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="poauthlimits" action="save"}
			{with model=$models.POAuthLimit legend="POAuthLimit Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{select attribute="username" options=$people label='Person' nonone=true}
				{select attribute="glcentre_id" options=$gl_centres label='GL Centre'}
				{input type='text' attribute='order_limit' }
			{/with}
				<dl id="view_data_left">
					<dt class="heading"><a href="#" class="ul-sort" data-sort-element="available_accounts">GL Account</a></dt>
						<div id="view_data_bottom">
							<div id="gl_accounts">
								{include file="./getaccounts.tpl"}
							</div>
						</div>
					</dt>
				</dl>
			{with model=$models.POAuthLimit legend="POAuthLimit Details"}
				<dl id="view_data_right">
					<dt class="heading"><a href="#" class="ul-sort" data-sort-element="selected_accounts">Selected Accounts</a></dt>
						<input type="hidden" id="accounts_text" >
						<div id="view_data_bottom">
							<div id="selected_accounts">
								{include file="./show_auth_accounts.tpl"}
							</div>
						</div>
					</dt>
				</dl>
				<div id="view_data_bottom">
					{submit value='Save Selection'}
				</div>
			{/with}
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
{/content_wrapper}