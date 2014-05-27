{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="modulecomponents" action="save"}
			<div id="view_data_left">
				{with model=$ModuleComponent}
  					{input type='hidden' attribute='id'}
					{view_data attribute="name" label='inernal_name'}
					{view_data label="type" value=$internal_type|cat:' '|cat:$ModuleComponent->getEnum('type', $ModuleComponent->type)}
					{view_data label='Version' value=$version}
					{input attribute='title'}
					{input attribute='description'}
					{if $ModuleComponent->type=='C'}
						{input attribute='help_link' class="website" }
					{/if}
				{/with}
			</div>
			<div id="view_data_bottom">
				{submit}
			</div>
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
{/content_wrapper}