{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<dl>
	<dd>{link_to module=$module submodule=$submodule controller=gltransactions action=new value="New Journal Entry" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=glaccounts  value="View General Ledger Accounts" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=glcentres  value="View General Ledger Centres" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=gltransactions  value="View General Ledger Transactions" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=glbalances  value="View Balances for current period" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=glbudgets value="View Budgets" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=glbalances action=trialbalance value="Trial Balance" } &raquo;</dd>
</dl>
