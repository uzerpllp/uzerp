{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}

	<div class="new_permission"></div>
	
	<script>
	
		$(document).ready(function() {
		
			$('.new_permission').uz_ajax({
				data: {
					module		: 'system_admin',
					controller	: 'permissions',
					action		: 'new'
				}
			});
			
		});
	
	</script>
	
	<ol class="permission_tree">
		{include file="./tree.tpl" tree=$tree}	
	</ol>
	
{/content_wrapper}