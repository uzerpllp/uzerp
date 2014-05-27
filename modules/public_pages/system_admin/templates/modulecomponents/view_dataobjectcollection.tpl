{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
		<div id="view_page" class="clearfix">
			{with model=$ModuleComponent}
  				{input type='hidden' attribute='id'}
				{view_data attribute="title"}
				{view_data attribute="description"}
				{view_data attribute="name" label='internal_name'}
				{view_data label="type" value=$internal_type|cat:' '|cat:$ModuleComponent->getEnum('type', $ModuleComponent->type)}
				{view_data label='Version' value=$version}
			{/with}
		</div>
{/content_wrapper}