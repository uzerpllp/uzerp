{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}

{if $block_form.form_id==''}
	{assign var=form_id value='save_form'}
{else}
	{assign var=form_id value=$block_form.form_id}
{/if}

<form enctype="multipart/form-data" id="{$form_id}" action="{$block_form.action}" method="{$block_form.method}" class="uz-validate {$block_form.class}" >

	<input type="hidden" name="original_action" value="{$block_form.original_action}" />
	
	{if $block_form.search_id}
		<input type="hidden" name="search_id" id="search_id" value="{$block_form.search_id}" />
	{/if}
	
	{if $block_form.submit_token_id}
		<input type="hidden" name="submit_token" id="submit_token_id" value="{$block_form.submit_token_id}" />
	{/if}
	
	{if $block_form.display_tags}
		<div id="view_page" class="clearfix">
	{/if}
	
	{$block_form.content}
	
	{if $block_form.display_tags}
		</div>
	{/if}
	
</form>