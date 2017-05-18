{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<dl>
	<dt>{"accounts"|prettify}</dt>
		<dd><img src="/assets/graphics/companys_small.png" alt="Companies" />{link_to module="contacts" controller="companys" value="all_accounts"} &raquo;</dd>
		<dd><img src="/assets/graphics/new_small.png" alt="New" />{link_to module="contacts" controller="companys" action="new" value="add_new_account"} &raquo;</dd>
		{*<dd><a href="#">Accounts added today &gt;</a></dd> *}
	<dt>{"leads"|prettify}</dt>
		<dd><img src="/assets/graphics/companys_small.png" alt="Leads" />{link_to module="contacts" controller="leads" value="all_leads"} &raquo;</dd>
		<dd><img src="/assets/graphics/new_small.png" alt="New" />{link_to module="contacts" controller="leads" action="new" value="add_new_lead"} &raquo;</dd>
		{*<dd><a href="#">Accounts added today &gt;</a></dd> *}
	<dt>{"people"|prettify}</dt>
		<dd><img src="/assets/graphics/persons_small.png" alt="People" />{link_to module="contacts" controller="persons" value="all_people"} &raquo;</dd>
		<dd><img src="/assets/graphics/new_small.png" alt="New" />{link_to module="contacts" controller="persons" action="new" value="add_new_person"} &raquo;</dd>
</dl>
