{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="complaintvolumes" action="save"}
		<dl id="view_data_left">
			{with model=$models.ComplaintVolume legend="Packs per Details"}
				{input type='hidden' attribute='id' }
				{view_section heading="complaint_sales_volume"}
					{input type='text' attribute='year' class="compulsory" }
					{input type='text' attribute='period' class="compulsory" }
					{input type='text' attribute='packs' class="compulsory" }
				{/view_section}
				{submit}
			{/with}
		</dl>
	{/form}
{content_wrapper}