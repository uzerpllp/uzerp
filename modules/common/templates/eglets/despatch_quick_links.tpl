{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
		<dd>{link_to module=$module submodule=$submodule controller=sodespatchlines value="View Despatch Notes" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=sodespatchlines action=viewbyorders value="View Orders for Despatch" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=whtransfers action=selectWHTransfers value="View Warehouse Transfers awaiting Despatch" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=whtransfers action=viewWHTransfers value="View Completed Warehouse Transfers" } &raquo;</dd>
</dl>
