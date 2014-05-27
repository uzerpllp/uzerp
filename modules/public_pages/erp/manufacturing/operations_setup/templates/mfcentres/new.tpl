{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="mfcentres" action="save"}
		{with model=$models.MFCentre legend="MFCentre Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='work_centre' class="compulsory" }
			{input type='text'  attribute='centre' class="compulsory" }
			{input type='text'  attribute='centre_rate' class="compulsory" }
			{input type='hidden' attribute='mfdept_id' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}