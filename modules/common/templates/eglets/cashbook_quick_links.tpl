{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
	<dd>{link_to module=$module submodule=$submodule controller=bankaccounts value="View Bank Accounts" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=cbtransactions value="View all Transactions" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=cbtransactions action=receive_payment value="Receive a payment" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=cbtransactions action=make_payment value="Make a payment" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=cbtransactions action=move_money value="Move money" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=periodicpayments value="Periodic Payments"} &raquo;</dd>
</dl>
