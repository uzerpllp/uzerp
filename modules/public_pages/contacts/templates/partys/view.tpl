{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$Company}
			{view_section heading="account_details"}
				{view_data attribute="name"}
				{view_data attribute="accountnumber"}
				{view_data attribute="owner"}
				{view_data attribute="assigned"}
				{view_data attribute="parent" label="parent_account"}
				<dt>{"categories"|prettify}</dt>
				{foreach name=categories item=category from=$categories}
					{if $smarty.foreach.categories.first}<dd>{/if}
					{$category->category}{if !$smarty.foreach.categories.last},{/if}
				{foreachelse}
					<dd class="blank">-
				{/foreach}		
				</dd>
			{/view_section}
			{view_section heading="contact_details"}
				{view_data attribute="phone"}
				{view_data attribute="fax"}
				{view_data attribute="email"}
				{view_data attribute="website"}
			{/view_section}
			{view_section heading="organisation_details"}
					{view_data attribute="creditlimit"}
					{view_data attribute="vatnumber"}
					{view_data attribute="companynumber"}
					{view_data attribute="employees"}
			{/view_section}
		{/with}
		</dl>
		<dl id="view_data_right">
			{view_section heading="address_details"}
				{with model=$Company->address}
					{view_data attribute="street1"}
					{view_data attribute="street2"}
					{view_data attribute="street3"}	
					{view_data attribute="town"}
					{view_data attribute="county"}
					{view_data attribute="postcode"}
					{view_data attribute="country"}
				{/with}
			{/view_section}
			{if $crm_access}
				{view_section heading="CRM_details"}
					{with model=$Company->crm}
						{view_data attribute="company_status"}
						{view_data attribute="company_source"}
						{view_data attribute="company_classification"}
						{view_data attribute="company_rating"}
						{view_data attribute="company_industry"}
						{view_data attribute="company_type"}
					{/with}
				{/view_section}
			{/if}
			{view_section heading="access_details"}
				{with model=$Company}
					{view_data attribute="created"}
					{view_data attribute="lastupdated"}
				{/with}
			{/view_section}
		</dl>
	</div>
{/content_wrapper}