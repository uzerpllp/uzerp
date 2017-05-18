{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.4 $ *}
	<div id="view_page" class="clearfix">
		{with model=$datadefinitiondetails}
			{view_data attribute="data_definition" link_to='"controller":"datadefinitions", "module":"edi", "action":"view", "id":"'|cat:$datadefinitiondetails->data_definition_id|cat:'"'}
			{view_data attribute="parent" link_to='"controller":"datadefinitiondetails", "module":"edi", "action":"view", "id":"'|cat:$datadefinitiondetails->parent_id|cat:'"'}
			{view_data attribute="element"}
			{view_data attribute="position"}
		{/with}
		{view_section heading='Maps to'}
			{with model=$datamap}
				{view_data attribute="name"}
				{view_data attribute="internal_type"}
				{view_data attribute="internal_attribute"}
			{/with}
		{/view_section}
		{view_section heading='Implements Translation Rule'}
			{with model=$datadefinitiondetails}
				{view_data attribute="name" value=$datamappingrule}
			{/with}
		{/view_section}
	</div>
{/content_wrapper}