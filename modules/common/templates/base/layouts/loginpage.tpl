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
<meta charset="UTF-8">
<html>
	<head>
		<title>Login</title>
		<link rel="stylesheet" type="text/css" href="/{$login_css}" />
		{if $user_css}
		<link rel="stylesheet" type="text/css" href="/{$user_css}" />
		{/if}
    	<script type="text/javascript" src="/assets/js/vendor/jquery-1.7.1.min.js"></script>
    	<script type="text/javascript" src="/{$module_js}"></script>
		</head>
	<body>
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
			<div class="form">
				{flash}
				{include file=$templateName}
			</div>
			<div class="footer">
				<p>{$config.BASE_TITLE} {$config.SYSTEM_VERSION} copyright &copy; <a href="http://www.uzerp.com/">uzERP LLP</a> 2007-{'Y'|date}</p>
				<p>uzERP is Free Software released under the GNU/GPL Licence.</p>
			</div>
		</div>
	</body>
</html>
{/if}
