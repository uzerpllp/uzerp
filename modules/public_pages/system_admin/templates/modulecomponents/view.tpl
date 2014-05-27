{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{if $internal_type=='Controller'}
	{include file='./view_controller.tpl'}
{elseif $internal_type=='DataObject'}
	{include file='./view_dataobject.tpl'}
{elseif $internal_type=='DataObjectCollection'}
	{include file='./view_dataobjectcollection.tpl'}
{/if}
