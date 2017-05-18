{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{foreach item=option key=key from=$options}
	{with group=$key}
		{with model=$option->contactmethod}
			{input type='hidden' attribute='id'}
			{input type='text' attribute="contact" label=$key}
		{/with}
		{with model=$option}
			{input type='hidden' attribute='id'}
			{input type='hidden' attribute='contactmethod_id'}
			{input type='hidden' attribute='party_id'}
			{input type='hidden' attribute='type'}
			{input type='hidden' attribute='name' value=$name}
			{input type='hidden' attribute='main' value=$main}
			{input type='hidden' attribute='billing' value=$billing}
			{input type='hidden' attribute='shipping' value=$shipping}
			{input type='hidden' attribute='payment' value=$payment}
			{input type='hidden' attribute='technical' value=$technical}
		{/with}
	{/with}
{/foreach}