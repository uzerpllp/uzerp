{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.6 $ *}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$parent_mapping_rule}
				{view_data attribute="name" label="parent_mapping rule" link_to='"module":"edi", "controller":"datamappingrules", "action":"view", "id":"'|cat:$parent_mapping_rule->id|cat:'"'}
			{/with}
			{with model=$parent_mapping}
				{view_data attribute="name" label='maps to' link_to='"module":"edi", "controller":"datamappings", "action":"view", "id":"'|cat:$parent_mapping->id|cat:'"'}
			{/with}
			{with model=$mapping_details}
				{view_data attribute="parent" label="parent value" value=$model->parent_detail->displayValue()}
			{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$mapping_rule}
				{view_data attribute="name" label="mapping rule" link_to='"module":"edi", "controller":"datamappingrules", "action":"view", "id":"'|cat:$mapping_rule->id|cat:'"'}
			{/with}
			{with model=$mapping}
				{view_data attribute="name" label='maps to' link_to='"module":"edi", "controller":"datamappings", "action":"view", "id":"'|cat:$mapping->id|cat:'"'}
			{/with}
			{with model=$mapping_details}
				{view_data attribute="internal_value" value=$model->displayValue()}
				{view_data attribute="external_code"}
			{/with}
		</dl>
	</div>
{/content_wrapper}