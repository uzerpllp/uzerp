{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.15 $ *}
{if $ajax == true}
LOGIN_TIMEOUT
{else}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Login</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="all" href="/resource.php?css&file=themes/{$theme}/css/reset.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/resource.php?css&file=modules/public_pages/login/resources/css/login.less" />
		<script type="text/javascript" src="/resource.php?js"></script>
		<script type="text/javascript" src="/resource.php?js&file=modules/public_pages/login/resources/js/login.js"></script>
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
				<p>{$config.BASE_TITLE} {$config.SYSTEM_VERSION} copyright &copy; <a href="http://www.uzerp.co.uk/">uzERP LLP</a> 2007-{'Y'|date}</p>
				<p>uzERP is Free Software released under the GNU/GPL Licence.</p>
			</div>
		</div>
	</body>
</html>
{/if}
