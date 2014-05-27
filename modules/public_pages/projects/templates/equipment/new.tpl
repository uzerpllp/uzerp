{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{with model=$models.ProjectEquipment legend="Equipment Details"}
		{form controller="equipment" action="save"}
			{input type='hidden'  attribute='id' }
			{input type='text' attribute='name'}
			{input type='text' attribute='setup_cost'}
			{input type='text' attribute='cost_rate'}
			{select attribute='uom_id'}
			{input type='checkbox' attribute='available' label='Available'}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	{/with}
{/content_wrapper}