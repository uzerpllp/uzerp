{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{form controller="sodespatchevents" action="save" class="uz-validate"}
		{with model=$model }
			{input type='hidden'  attribute='id' }
			{input type='text' attribute='title' class="uz-validate-required"}
			{datetime attribute='start_time' class="uz-validate-required" }
			{datetime attribute='end_time' class="uz-validate-required" }
			{select attribute='status' class="uz-validate-required" } 
			{submit id='saveform' name='save' value='Save' class="uz-validate"}
		{/with}
	{/form}
{/content_wrapper}