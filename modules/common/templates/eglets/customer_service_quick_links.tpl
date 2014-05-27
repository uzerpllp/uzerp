{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
	<dt>{"Customer Service"|prettify}</dt>
		<dd>{link_to module=$module submodule=$submodule controller=customerservices value="Customer Service Summary"} &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=customerservices action=failurecodesummary value="Customer Service Failure Code Summary"} &raquo;</dd>
		<dd>{link_to module=$module submodule=$submodule controller=csfailurecodes value="Customer Service Failure Codes"} &raquo;</dd>
</dl>
