{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.20 $ *}
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<title>{$config.BASE_TITLE} {$config.SYSTEM_VERSION}</title>
	
	<link rel="stylesheet" type="text/css" href="/resource.php?css" />
	
	{foreach item=css from=$module_css}
		<link rel="stylesheet" type="text/css" href="/resource.php?css&file={$css}" />
	{/foreach}
	
	<script type="text/javascript">
		var themeName='{$theme}';
		var action="{$action}";
		var scriptFiles=[];
	</script>
	
	<script type="text/javascript" src="/resource.php?js"></script>
	
	<script type="text/javascript">
		function loadScript(file){

			if (scriptFiles.indexOf(file) != -1) {
				return;
			}
			
			var script = document.createElement("script")
			script.type = "text/javascript";

			script.src = "/resource.php?js&file="+file;
			document.getElementsByTagName("head")[0].appendChild(script);
			
			scriptFiles[scriptFiles.length]=file;
		}	
	</script>

</head>