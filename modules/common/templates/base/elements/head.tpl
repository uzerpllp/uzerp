{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.20 $ *}
<head>
        <meta charset="utf-8" />
	<meta content="{$csrf_token}" name="csrf-token" />
	<title>{$config.BASE_TITLE} {$config.SYSTEM_VERSION}</title>
	
	<link rel="stylesheet" type="text/css" href="/{$main_css}" />
	{if $user_css}
	<link rel="stylesheet" type="text/css" href="/{$user_css}" />
	{/if}
	
	<script type="text/javascript" src="/assets/js/vendor/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="/assets/js/vendor/jquery-ui-1.9.2.custom.min.js"></script>
	<script type="text/javascript" src="/{$main_js}"></script>
	{if $module_js}
	<script type="text/javascript" src="/{$module_js}"></script>
	{/if}	
</head>

