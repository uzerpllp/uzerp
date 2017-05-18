{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}

<div id="{$identifier}" class="pie chart" style="height: 200px; width: 500px;"></div>

<script type="text/javascript">
	
	$(document).ready(function() {
		
		var options = {$options};
		
		options.seriesList = chart_convert_dates(options.seriesList);
		
		$.uz_pie_chart(options);
	
	});
	
</script>