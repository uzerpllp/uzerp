<h2>Login</h2>
<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=login" method="post" >
    <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
		{if isset($smarty.cookies.username)}
			<input title="Enter username" name="username" type="text" class="required" placeholder="Username" value="{$smarty.cookies.username}" />
		{else}
			<input title="Enter username" name="username" type="text" class="required" placeholder="Username" autofocus />
		{/if}
		<input title="Enter password" name="password" type="password" class="required" placeholder="Password" autocomplete="off" {if isset($smarty.cookies.username)}autofocus{/if}/>
	<input type="submit" class="submit {$submit_class}" value="Log In">
	{foreach name=request item=item key=key from=$controller_data}
		<input type="hidden" name="{$key}" value="{$item}" />
	{/foreach}
	<input type="hidden" id="rememberUser" name="rememberUser" value="true" />
</form>
