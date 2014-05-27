{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{foreach item=model from=$collection}
	{* $Revision: 1.6 $ *}
	<li>
		{if $model->map_to_type!='' || $model->map_to_attribute!=''}
			{if $model->map_to_type!='' && $model->map_to_attribute!=''}
				{assign var=points_to value='-->'}
			{else}
				{assign var=points_to value=''}
			{/if}
			{assign var=maps_to value="<strong>Maps to</strong> {$model->map_to_type} $points_to {$model->map_to_attribute}"}
		{else}
			{assign var=maps_to value=""}
		{/if}
		{if $model->mapping_rule!=''}
			{assign var=implements value="Implements Rule"}
		{else}
			{assign var=implements value=""}
		{/if}
		{if $model->default_value!=''}
			{assign var=default_value value="<strong>Default Value '{$model->default_value}' </strong>"}
		{else}
			{assign var=default_value value=""}
		{/if}
		{if $model->$children->count()>0}
			<strong>{link_to module="$module" controller="datadefinitiondetails" action="edit" data_definition_id=$model->data_definition_id id=$model->id value=$model->element }</strong> : {link_to module=$module controller='datadefinitiondetails' action='new' data_definition_id=$model->data_definition_id parent_id=$model->id value="[Add Child]" } {link_to module=$module controller='datadefinitiondetails' action='delete' id=$model->id data_definition_id=$model->data_definition_id value="[Delete]" } {$maps_to} <strong>{$implements}</strong> {link_to module="$module" controller="datamappingrules" action="view" id=$model->data_mapping_rule_id value=$model->mapping_rule }
			<ul class="{$class_name}">
				{include file='./definition_tree.tpl' collection=$model->$children children=$children parent_id=$id class_name=$class_name roleid=$roleid}
			</ul>
		{else}
			<strong>{link_to module="$module" controller="datadefinitiondetails" action="edit" data_definition_id=$model->data_definition_id id=$model->id value=$model->element }</strong> : {link_to module=$module controller='datadefinitiondetails' action='new' data_definition_id=$model->data_definition_id parent_id=$model->id value="[Add Child]" } {link_to module=$module controller='datadefinitiondetails' action='delete' id=$model->id data_definition_id=$model->data_definition_id value="[Delete]" } {$maps_to} <strong>{$implements}</strong> {link_to module=$module controller=datamappingrules action='view' id=$model->data_mapping_rule_id value=$model->mapping_rule } {$default_value}
		{/if}
	</li>
{/foreach}