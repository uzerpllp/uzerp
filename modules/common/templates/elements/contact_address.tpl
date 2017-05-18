{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{include file='elements/select_address.tpl' address_id=$partyaddress->address_id}
{with model=$partyaddress}
	{input type='hidden' attribute='id'}
	{input type='hidden' attribute='address_id'}
	{input type='hidden' attribute='party_id'}
	{input type='hidden' attribute='name' value=$name}
	{input type='hidden' attribute='main' value=$main}
	{input type='hidden' attribute='billing' value=$billing}
	{input type='hidden' attribute='shipping' value=$shipping}
	{input type='hidden' attribute='payment' value=$payment}
	{input type='hidden' attribute='technical' value=$technical}
{/with}