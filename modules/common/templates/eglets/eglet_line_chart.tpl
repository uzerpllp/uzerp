{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}

<div id="{$identifier}" style="height: 200px; width: 500px;"></div>

<script type="text/javascript">

    $(document).ready(function() {
		
		var options	= {$options};
		
		options.seriesList = chart_convert_dates(options.seriesList);
		
		$.uz_line_chart(options);
		
	});
	
</script>