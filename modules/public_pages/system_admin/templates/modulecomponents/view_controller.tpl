{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{with model=$ModuleComponent}
		<div id="view_page" class="clearfix">
			{view_section heading='Details' expand="open"}
				{input type='hidden' attribute='id'}
				{view_data attribute="title"}
				{view_data attribute="description"}
				{view_data attribute="name" label='internal_name'}
				{view_data label="type" value=$ModuleComponent->getEnum('type', $ModuleComponent->type)}
				{view_data label='Version' value=$version}
				{view_data attribute="help_link"}
			{/view_section}
			{view_section heading='Local Methods' expand="open"}
				{foreach item=method from=$local_methods}
					{view_data value=$method}
				{foreachelse}
					Nothing defined
				{/foreach}
			{/view_section}
			{view_section heading='Inherited Methods' expand="closed"}
				{foreach item=method from=$inherited_methods}
					{view_data value=$method}
				{foreachelse}
					Nothing defined
				{/foreach}
			{/view_section}
		</div>
	{/with}
{/content_wrapper}