{* Used to report fatal errors to the user - index.php *}
{if !$xhr}
{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/head.tpl"}
<body>
{/if}
<div id=flash class="sod">
	<ul id="errors">
		<li class="logo">
			<img src="/assets/graphics/logo.png" />
		</li>
		<li>
			<h1>Sorry, something went wrong in uzERP</h1>
			{if $event_id}
			<p>Please call support and include the following reference ID in your problem report: <strong>{$event_id}</strong></p>
			{else}
			<p><strong>{$exception_message}</strong></p>
			<p id="view_page"><em>Please notify your administrator.</em></p>
			{/if}
			{if $support_email}
				<a class="button report" href="mailto:{$support_email}?subject=uzERP%20Error&body={$email_body}">Send Report by Email</a>
			{/if}
		</li>
	</ul>
</div>
{if !$xhr}
{include file="file:{$smarty.const.BASE_TPL_ROOT}elements/footer.tpl"}
</body>
{/if}