{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
<dl>
		<dd>{link_to module=costing controller=stcosts value="View Cost History" } &raquo;</dd>
		<dd>{link_to module=costing controller=stcosts action=rollOver value="Roll-over Stock Items" _onclick="return confirmRollOver();" } &raquo;</dd>
		<dd>{link_to module=costing controller=stcosts action=recalcLatestCosts value="Re-calcuate Latest Costs" } &raquo;</dd>
</dl>