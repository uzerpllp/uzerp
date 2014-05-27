{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="SupplementaryComplaintCodes" action="save"}
		{with model=$models.SupplementaryComplaintCode legend="SupplementaryComplaintCode Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{select  attribute='complaint_code_id' class="compulsory" }
			{input type='text'  attribute='code' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}