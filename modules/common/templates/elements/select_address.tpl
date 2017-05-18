{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{with model=$address}
	{select attribute='fulladdress' nonone=true options=$addresses value=$address_id label='Address'}
	{if $address->fulladdress==''}
		<div id='address'>
	{else}
		<div id='address' style="display: none;">
	{/if}
	{include file='elements/address.tpl' }
	</div>
{/with}