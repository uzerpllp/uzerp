{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<dl>
	<dt>{"system_companies"|prettify}</dt>
		<dd><img src="/themes/default/graphics/companys_small.png" alt="Companies" />{link_to module="system_admin" controller="systemcompanys" value="all_system_companies"} &raquo;</dd>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="system_admin" controller="systemcompanys" action="new" value="add_new_system_company"} &raquo;</dd>
	<dt>{"user_company_access"|prettify}</dt>
		<dd><img src="/themes/default/graphics/persons_small.png" alt="Users" />{link_to module="system_admin" controller="usercompanyaccesss" value="all_user_company_access"} &raquo;</dd>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="system_admin" controller="usercompanyaccesss" action="new" value="add_new_user_company_access"} &raquo;</dd>
	<dt>{"permissions"|prettify}</dt>
		<dd><img src="/themes/default/graphics/persons_small.png" alt="Permissions" />{link_to module="system_admin" controller="permissions" value="all_permissions"} &raquo;</dd>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="system_admin" controller="permissions" action="new" value="add_new_permission"} &raquo;</dd>
	<dt>{"injector_classes"|prettify}</dt>
		<dd><img src="/themes/default/graphics/position.png" alt="Permissions" />{link_to module="system_admin" controller="injectorclasss" value="all_injector_classes"} &raquo;</dd>
		<dd><img src="/themes/default/graphics/new_small.png" alt="New" />{link_to module="system_admin" controller="injectorclasss" action="new" value="add_new_injector_class"} &raquo;</dd>
</dl>
