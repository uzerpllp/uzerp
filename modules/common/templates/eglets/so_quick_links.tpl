{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<dl>
	<dd>{link_to module=$module submodule=$submodule controller=soproductlines value="View SO Product Lines" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=soproductlines action= viewbyitems value="View Supply/Demand" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=sorders value="View Sales Orders" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=sorders action=new value="New Sales Order" type='O'} &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=sorders action=new value="New Sales Quote" type='Q'} &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=sorders action=viewByOrders value="View Availability By Sales Orders" } &raquo;</dd>
	<dd>{link_to module=$module submodule=$submodule controller=sorders action=viewByItems value="View Availability By Items For Sales Orders" } &raquo;</dd>
</dl>
