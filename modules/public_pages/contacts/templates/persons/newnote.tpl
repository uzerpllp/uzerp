{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}	
{content_wrapper}
	{form controller="persons" action="savenote"}
		<dl class="float-left">
			{with model=$PersonNote legend="Note Details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='text'  attribute='title' }
				{select attribute='person_id'}
				{textarea attribute='note'}
			{/with}
			{submit}
		</dl>
	{/form}
{content_wrapper}