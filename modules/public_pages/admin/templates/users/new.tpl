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
			<dl id="view_data_left">
				{view_section_h2 heading="user_details"}
				<div class="viewgrid">
					{input type='text'  attribute='username' class="compulsory" }
					{select attribute="person_id" options=$people}
					{input type='password' attribute='password'}
					{input type='text' attribute='email'}
					{input type='checkbox' attribute='access_enabled'}
					{input type='hidden' attribute='lastcompanylogin'}
				</div>
				{/view_section_h2}

				{view_section_h2 heading="Company Access and Roles"}
				<div class="viewgrid">
					<dt>
						<label for="user_roles">Roles for {$smarty.const.SYSTEM_COMPANY}</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[roles][]" id="user_roles" multiple="multiple">
							{html_options options=$roles selected=$current}
						</select>
					</dd>
					<dt>
						<label for="user_companies">System Companies</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[companies][]" id="user_companies" multiple="multiple">
							{html_options options=$companies selected=$selected_companies}
						</select>
					</dd>
					<div/>
				{/view_section_h2}
			</dl>


			<dl id="view_data_right">
				{if (isset($mfa_used))}{view_section_h2 heading="MFA Settings"}
				<div class="viewgrid">
					{view_data attribute='mfa_enrolled' label="MFA Enrolled"}
					{if $model->mfa_enrolled === 't'}
					{input type='checkbox' attribute='mfa_enabled' label="MFA Enabled" help="123"}
					<dt></dt>
					<dd><p class="help">If MFA is disabled it will not be required for this user on next login. <br>MFA will be required for subsequent logins.<p></dd>
					<dt></dt>
					<dd>
						<button id="reset-mfa" type="button">Reset MFA Enrollment</button>
						<p class="help">If the users enrollment is reset they will be asked to enroll again on next login.</p>
					</dd>
					{/if}
				</div>
				{/view_section_h2}{/if}
				
				{view_section_h2 heading="Audit and Debug Details"}
				<div class="viewgrid">
					{input type='checkbox' attribute='audit_enabled'}
					{input type='checkbox' attribute='debug_enabled'}
					<input type="hidden" name="DebugOption[id]" id="debug_id" value={$debug_id}>
					<dt>
						<label for="debug_options">Debug Options</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[debug_options][]" id="debug_options" multiple="multiple">
							{html_options options=$debug_options selected=$selected_options}
						</select>
					</dd>
				</div>
				{/view_section_h2}
			</dl>
			</div>

			<dl class="form-actions">
				{submit another=false}
			</dl>
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}