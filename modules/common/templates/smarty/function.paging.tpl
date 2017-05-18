{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

<div id="data_grid_header" class="clearfix">
	
	{if $num_pages > 1}
	
		<div class="pagination" >
		    <a href="#" class="first" data-action="first">&laquo;</a>
		    <a href="#" class="previous" data-action="previous">&lsaquo;</a>
		    <input type="text" readonly="readonly" data-current-page="{$cur_page}" data-max-page="{$num_pages}" data-url="{$function_paging.paging_link}"/>
		    <a href="#" class="next" data-action="next">&rsaquo;</a>
		    <a href="#" class="last" data-action="last">&raquo;</a>
		</div>
	
	{/if}

</div>