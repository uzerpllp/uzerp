{** 
 * logout.tpl displayed on logout for non-interactive login handlers
 *
 * @package login
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **}
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
		<script type="text/javascript" src="/{$module_js}"></script>
    </head>
    <body>
        {if isset($info_message) && $info_message}
            {include file="file:{$smarty.const.STANDARD_TPL_ROOT}elements/info_message.tpl"}
        {/if }
        <div id="login">
            <div class="header">
                <div><img src="/data/company1/logos/logo.png" /></div>
                <h2>You've been logged out</h2>
                <a class="button" href="/">Log in &raquo;</a>
            </div>
            <div class="form">
            	{flash}
            </div>
            <div class="footer">
                <p>{$config.BASE_TITLE} {$config.SYSTEM_VERSION} copyright &copy; <a href="http://www.uzerp.com/">uzERP LLP</a> 2007-{'Y'|date}</p>
                <p>uzERP is Free Software released under the GNU/GPL Licence.</p>
            </div>
        </div>
    </body>
</html>
