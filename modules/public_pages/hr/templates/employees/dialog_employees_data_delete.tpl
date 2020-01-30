<div id="dialog-form-container" class="dialog-form" title="Delete Employee Personal Data">
  <form id="delete-data-form" method="post" action="/?module=hr&controller=employees&action=deletePersonalData&id=12">
    <fieldset>
        <legend class="formgrid-legend">Please Select data to be deleted for employee <strong>{$Employee->Person->getIdentifierValue()}</strong></legend>
        <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
        <input type="hidden" id="employee[id]" value="{$Employee->id}">
        <div class="formgrid">
            {foreach from=$Employee->getPersonalDataFields() key=$data_item item=$params}
                <input type="checkbox" id="employee-{$data_item}" name="employee[{$data_item}]">
                <label for="employee[{$data_item}]">{$params.label}</label>
            {/foreach}
            <input type="checkbox" id="employee-contact-methods" name="employee[contact_methods]">
            <label for="employee[contact_methods]">Phone numbers and email addresses</label>
            <input type="checkbox" id="employee-addresses" name="employee[addresses]">
            <label for="employee[adresses]">Postal addresses</label>
            <!-- Allow form submission with keyboard without duplicating the dialog button -->
            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
        </div>
    </fieldset>
  </form>
</div>