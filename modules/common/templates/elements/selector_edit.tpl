{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper title="$action $description "|cat:$SelectorObject->name}
	{form controller=$controller action="save"}
		{with model=$models.SelectorObject}
			{foreach item=item key=key from=$parent}
				{view_data attribute=name value=$item.name label=$item.description link_to='"module":"'|cat:$module|cat:'","controller":"'|cat:$controller|cat:'","action":"edit","id":"'|cat:$key|cat:'"'}
			{/foreach}
			{input type='hidden' attribute='id' }
			{input type='text' attribute='name' class="compulsory" label=$description}
			{input type='hidden' attribute='parent_id' value=$parent_id }
			{input type='hidden' attribute='description' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
	{if $collection|count > 0}
		<div id="view_page" class="clearfix">
			<dl id="view_data_left">
				{view_section heading="Current $description"|cat:'s'}
				{/view_section}
			</dl>
		</div>
		{include file="elements/datatable.tpl"}
	{/if}
{/content_wrapper}