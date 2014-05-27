{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
	<dd>{link_to module=$module submodule=$submodule controller=poproductlines value="View PO Product Lines" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=poproductlines action="viewbyitems" value="View Supply/Demand" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=porders value="View Purchase Orders" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=porders action=new value="New Purchase Order"} &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=porders action=createinvoice value="Create Purchase Invoice"} &raquo;</dd>
</dl>
