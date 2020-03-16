{content_wrapper}
<form id="save_form" class="gridform" id="pallet-label" action="/?module=despatch&controller=sodespatchlines&printaction=printPalletLabel&action=printdialog" enctype="multipart/form-data" method="post">
     <fieldset class="">
     <legend><p><em>All information must be specified</em></p></legend>
        <div class="formgrid">
        
        <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
        
        <select id="labelname" name="labelname">
        {foreach from=$label_options item=$item key=$key}
            <option value="{$key}" {if $form_data['labelname'] == $key}selected{/if}>{$item}</option>
        {/foreach}
        </select>
        <label for="labelname">Label type:</label>
        <select id="item" name="item">
        {foreach from=$customer_items item=$item key=$key}
            <option value="{$key}" {if $form_data['item'] == $key}selected{/if}>{$item}</option>
        {/foreach}
        </select>
        <label for="item">Item:</label>
        <input id="items" name="items" type="text" {if $form_data['items']}value="{$form_data['items']}"{/if}>
        <label for="labelname">Total Number of Items:</label>
        <input id="count" name="count" type="text" {if $form_data['count']}value="{$form_data['count']}"{/if}>
        <label for="count">Case Count:</label>
        <input id="batchlot" name="batchlot" type="text" {if $form_data['batchlot']}value="{$form_data['batchlot']}"{/if}>
        <label for="batchlot">Batch/Lot:</label>
        <input id="pallet-counter" name="pallet-counter" type="text" {if $form_data['pallet-counter']}value="{$form_data['pallet-counter']}"{/if}>
        <label for="pallet-counter">Pallet Counter:</label>
        <input id="remember-data" name="remember-data" type="checkbox"{if $form_data['remember-data']=='on'} checked{/if}>
        <label for="remember-data">Keep form input for next label</label>
        <input id="increment-counter" name="increment-counter" type="checkbox"{if $form_data['increment-counter']=='on'} checked{/if}>
        <label for="increment-counter">Increment Pallet Counter</label>
        <div class="form-buttons">
        <button id="search_print" type="submit">Output</button>
        <button id="clear-form" type="button" name="clearform">Reset Form</button>
        </div>
        </div>
    </fieldset>
</form>
{/content_wrapper}