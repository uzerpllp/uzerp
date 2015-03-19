{* Used to report fatal errors to the user - index.php *}
{include file="file:{$smarty.const.THEME_ROOT}{$theme}default/elements/head.tpl"}
<div id=flash class="sod">
	<ul id="errors">
		<li class="logo">
			<img src="themes/{$theme}default/graphics/logo.png" />
		</li>
		<li>
			<h1>Sorry, something went wrong in uzERP</h1>
			{if $event_id}
			<p>Please call support and include the following reference ID in your problem report: <strong>{$event_id}</strong></p>
			{else}
			<p>{$exception_message}</p>
			<p id="view_page"><strong>Please notify your administrator.</strong></p>
			{/if}
			{if $support_email}
				<a class="button report" href="mailto:{$support_email}?subject=uzERP%20Error&body={$email_body}">Send Report by Email</a>
			{/if}
		</li>
	</ul>
</div>
{include file="file:{$smarty.const.THEME_ROOT}{$theme}default/elements/footer.tpl"}
