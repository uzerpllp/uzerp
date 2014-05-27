{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<dl>
		<dd>{link_to module=$module submodule=$submodule controller=whStores value="Stores" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=stitems value="Stock Items" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=sttransactions value="Stock Transactions" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=mfworkorders action=new value="Add a new Works Order" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=Mfworkorders value="View Works Orders" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=whactions action=actionsMenu value="Stock Transfer Actions" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=whtransfers action=index value="Warehouse Transfer" } &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=sttransactions action=index status=E value="View Backflush Errors" } &raquo;</dd>
</dl>
