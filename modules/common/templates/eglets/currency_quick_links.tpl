{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
	<dt>{"view_currency_items"|prettify}</dt>
		<dd>{link_to module=$module submodule=$submodule controller=currencys value="currencies"} &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=currencyrates value="currency_rates"} &raquo;</dd>
	<dt>{"add_new_currency_items"|prettify}</dt>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="$module" submodule="$submodule" controller="currencys" action="new" value="add_new_currency"} &raquo;</dd>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="$module" submodule="$submodule" controller="currencyrates" action="new" value="add_new_currency_rate"} &raquo;</dd>
		{*<dd><a href="#">My Accounts &gt;</a></dd>
		<dd><a href="#">Accounts added today &gt;</a></dd> *}
</dl>
