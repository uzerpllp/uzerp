{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="opportunitynotes" action="save"}
		{with model=$models.OpportunityNote legend="OpportunityNote Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='title' class="compulsory" }
			{select attribute='opportunity_id' }
			{textarea attribute='note' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}