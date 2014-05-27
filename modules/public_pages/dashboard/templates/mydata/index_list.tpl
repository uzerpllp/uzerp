{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

<div class='placeholder'>
	<strong>
		Folders: {$mydata.directory|count}
		Files: {$mydata.file|count}
	</strong>
	{foreach item=item key=key from=$mydata}

		{if $key=='directory'}
	
			{foreach item=directory key=dirname from=$item}
				<dt data-type="{$dirname}" class="expand heading closed">{$dirname}</dt>
					<div class="hidden">
						{include file="./index_list.tpl" mydata=$directory parent_id=$dirname class_name=$class_name}
					</div>
				</dt>
			{/foreach}
			
		{/if}
	
	{/foreach}
</div>