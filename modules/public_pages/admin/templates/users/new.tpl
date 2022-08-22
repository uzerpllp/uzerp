{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="users" action="save"}
		{with model=$models.User legend="User Details"}
			<div class="form-columns">
				<div class="col">
					<h2 class="heading">User Details</h2>
					<dl class="viewgrid">
						{input type='text'  attribute='username' class="compulsory" }
						{select attribute="person_id" options=$people}
						{input type='password' attribute='password'}
						{input type='text' attribute='email'}
						{input type='checkbox' attribute='access_enabled'}
						{input type='hidden' attribute='lastcompanylogin'}
					</dl>

					<h2 class="heading">Company Access and Roles</h2>
					<dl class="viewgrid">	
						<dt>
							<label for="user_roles">Roles for {$smarty.const.SYSTEM_COMPANY}</label>
						</dt>
						<dd class="for_multiple">
							<select name="User[roles][]" id="user_roles" multiple>
								{html_options options=$roles selected=$current}
							</select>
						</dd>
						<dt>
							<label for="user_companies">System Companies</label>
						</dt>
						<dd class="for_multiple">
							<select name="User[companies][]" id="user_companies" multiple>
								{html_options options=$companies selected=$selected_companies}
							</select>
						</dd>
					</dl>
				</div>

				<div class="col">
					{if (isset($mfa_used))}
					<h2 class="heading">MFA Settings</h2>
					<dl class="viewgrid">
						{view_data attribute='mfa_enrolled' label="MFA Enrolled"}
						{if $model->mfa_enrolled === 't'}
						{input type='checkbox' attribute='mfa_enabled' label="MFA Enabled"}
						<dt></dt>
						<dd><p class="help">If MFA is disabled it will not be required for this user on next login. <br>MFA will be required for subsequent logins.<p></dd>
						{/if}
					</dl>
					{/if}
				
					<h2 class="heading">Audit and Debug Details</h2>
					<dl class="viewgrid">
						{input type='checkbox' attribute='audit_enabled'}
						{input type='checkbox' attribute='debug_enabled'}
						<input type="hidden" name="DebugOption[id]" id="debug_id" value={$debug_id}>
						<dt>
							<label for="debug_options">Debug Options</label>
						</dt>
						<dd class="for_multiple">
							<select name="User[debug_options][]" id="debug_options" multiple>
								{html_options options=$debug_options selected=$selected_options}
							</select>
						</dd>
					</dl>
				</div>
			</div>
		{/with}
		<dl class="form-actions">
		{submit another=false}
		</dl>
	{/form}
{/content_wrapper}