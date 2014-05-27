{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="ComplaintCodes" action="save"}
		{with model=$models.ComplaintCode legend="ComplaintCode Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='code' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}