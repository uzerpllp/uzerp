{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.15 $ *}
{if $ajax == true}
LOGIN_TIMEOUT
{else}
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Login</title>
		<link rel="stylesheet" type="text/css" href="/{$login_css}" />
		{if $user_css}
		<link rel="stylesheet" type="text/css" href="/{$user_css}" />
		{/if}
    	<script type="text/javascript" src="/assets/js/vendor/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="/assets/js/vendor/clipboardjs/clipboard.min.js"></script>
    	<script type="text/javascript" src="/{$module_js}"></script>
	</head>
	<body class="module-{$module|replace:'_':'-'} controller-{$controller|replace:'_':'-'}{if $action} action-{$action|ltrim:'_'|replace:'_':'-'}{/if}">
		{if isset($info_message) && $info_message}
			{include file="file:{$smarty.const.STANDARD_TPL_ROOT}elements/info_message.tpl"}
		{/if }
		<div id="login">
			<div class="header">
				<div><img src="/data/company1/logos/logo.png" /></div>
				{if $config.SYSTEM_STATUS !== ''}
					<h2>{$config.SYSTEM_STATUS}</h2>
				{/if}
			</div>
			<div class="form {$action}">
				{flash}
				{include file=$templateName}
			</div>

		</div>
	</body>
</html>
{/if}
