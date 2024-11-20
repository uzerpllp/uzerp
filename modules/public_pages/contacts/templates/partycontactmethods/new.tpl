{** 
 *	(c) 2024 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="partycontactmethods" action="save"}
			{with model=$models.PartyContactMethod legend="models.PartyContactMethod Details"}
				{with model=$model->contactmethod}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{if $PartyContactMethod->type eq 'T'}
						{input type='text'  attribute='contact' label='Telephone number' class="compulsory" }
					{elseif $PartyContactMethod->type eq 'F'}
						{input type='text'  attribute='contact' label='Fax number' class="compulsory" }
					{elseif $PartyContactMethod->type eq 'M'}
						{input type='text'  attribute='contact' label='Mobile' class="compulsory" }
					{elseif $PartyContactMethod->type eq 'E'}
						{input type='text'  attribute='contact' label='Email address' class="compulsory" }
					{else}
						{input type='text'  attribute='contact' class="compulsory" }
					{/if}
				{/with}
				{with model=$PartyContactMethod}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{input type='hidden'  attribute='contactmethod_id' }
					{input type='hidden' attribute='party_id' }
					{input type='text'  attribute='name' }
					{input type='hidden'  attribute='type' class="compulsory" }
					{if $model->main=='t'}
						{view_data attribute='main' }
					{else}
						{input type='checkbox'  attribute='main' }
					{/if}
					{input type='checkbox'  attribute='billing' }
					{input type='checkbox'  attribute='shipping' }
					{input type='checkbox'  attribute='payment' }
					{input type='checkbox'  attribute='technical' }
					{if (isset($editing_sysco) && $editing_sysco == true && $PartyContactMethod->type == 'E') || (isset($editing_sysco) && $editing_sysco == true && $controller_data.type == 'E')}
						<dt>
						<label for="partycontactmethod_email_trailer">Email Trailer:</label>
					</dt>
					<dd>
				<textarea cols="30" rows="5" name="PartyContactMethod[email_trailer]" id="partycontactmethod_email_trailer" placeholder="Leave blank for system default">{$PartyContactMethod->email_trailer}</textarea>
				
					</dd>
					{/if}
				{/with}
			{/with}
			{submit}
		{/form}
		{include file="elements/cancelForm.tpl"}
	</dl>
{/content_wrapper}