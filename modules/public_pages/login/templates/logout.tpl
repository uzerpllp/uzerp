{** 
 * logout.tpl displayed on logout for non-interactive login handlers
 *
 * @package login
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **}
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
