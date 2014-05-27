{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{foreach key=name item=model from=$collection}
	{* $Revision: 1.3 $ *}
	{if $model->nodeType == $XML_TEXT_NODE}
		{if $internal_code<>'' || $display_value<>''}
			{if $internal_code!=$display_value}
				{assign var=display_value value=' ('|cat:$display_value|cat:')'}
			{else}
				{assign var=display_value value=''}
			{/if}
			{assign var=value value=$model->nodeValue|cat:'->'|cat:$internal_code|cat:$display_value}
		{else}
			{assign var=value value=$model->nodeValue}
		{/if}
		{if $data_mapping_rule_id<>''}
			{if $id<>''}
				{link_to module='edi' controller='datamappingdetails' action='edit' id=$id value=$value}
			{else}
				{link_to module='edi' controller='datamappingdetails' action='new' data_mapping_rule_id=$data_mapping_rule_id external_code=$external_code value=$value}
			{/if}
		{else}
			{$value}
		{/if}
	{else}
		<ul {$class}>
			<li>
				{assign var=line_no value=''}
				{assign var=id value=''}
				{assign var=data_mapping_rule_id value=''}
				{assign var=internal_code value=''}
				{assign var=display_value value=''}
				{if $model->hasAttributes()}
					{foreach item=attr from=$model->attributes}
						{if $attr->name=='line_no'}
							{assign var=line_no value=$attr->value}
						{/if}
						{if $attr->name=='id'}
							{assign var=id value=$attr->value}
						{/if}
						{if $attr->name=='data_mapping_rule_id'}
							{assign var=data_mapping_rule_id value=$attr->value}
						{/if}
						{if $attr->name=='external_code'}
							{assign var=external_code value=$attr->value}
						{/if}
						{if $attr->name=='internal_code'}
							{assign var=internal_code value=$attr->value}
						{/if}
						{if $attr->name=='display_value'}
							{assign var=display_value value=$attr->value}
						{/if}
					{/foreach}
				{/if}
				<strong>{$model->tagName} {$line_no}</strong>
				{if $model->hasChildNodes()}
					{include file=$data_tree collection=$model->childNodes class=''}
				{else}
					{if $internal_code<>''}
						{if $display_value<>'' && $internal_code!=$display_value}
							{assign var=display_value value=' ('|cat:$display_value|cat:')'}
						{else}
							{assign var=display_value value=''}
						{/if}
						{assign var=value value=$model->nodeValue|cat:'->'|cat:$internal_code|cat:$display_value}
					{else}
						{assign var=value value=$model->nodeValue}
					{/if}
					{if $data_mapping_rule_id<>''}
						{if $id<>''}
							{link_to module='edi' controller='datamappingdetails' action='edit' id=$id value=$value}
						{else}
							{link_to module='edi' controller='datamappingdetails' action='new' data_mapping_rule_id=$data_mapping_rule_id external_code=$external_code value=$value}
						{/if}
					{else}
						{$value}
					{/if}
				{/if}
			</li>
		</ul>
	{/if}
{/foreach}