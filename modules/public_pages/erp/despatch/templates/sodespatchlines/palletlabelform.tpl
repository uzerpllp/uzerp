{content_wrapper}
<form id="save_form" class="" id="pallet-label" action="/?module=despatch&controller=sodespatchlines&printaction=printPalletLabel&action=printdialog" enctype="multipart/form-data" method="post">
     <fieldset id="view_page" class="">
     <legend><p><em>All information must be specified</em></p></legend>
        <div class="form-columns">
            <dl class="viewgrid">
                <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
                <dt><label for="labelname">Label type:</label></dt>                
                <dd><select id="labelname" name="labelname">
                {foreach from=$label_options item=$item key=$key}
                    <option value="{$key}" {if $form_data['labelname'] == $key}selected{/if}>{$item}</option>
                {/foreach}
                </select></dd>

                <dt><label for="item">Item:</label></dt>
                <dd><select id="item" name="item">
                {foreach from=$customer_items item=$item key=$key}
                    <option value="{$key}" {if $form_data['item'] == $key}selected{/if}>{$item}</option>
                {/foreach}
                </select></dd>

                <dt><label for="items">Total Number of Items:</label></dt>
                <dd><input id="items" name="items" type="text" {if $form_data['items']}value="{$form_data['items']}"{/if}></dd>

                <dt><label for="count">Case Count:</label></dt>
                <dd><input id="count" name="count" type="text" {if $form_data['count']}value="{$form_data['count']}"{/if}></dd>

                <dt><label for="batchlot">Batch/Lot:</label></dt>
                <dd><input id="batchlot" name="batchlot" type="text" {if $form_data['batchlot']}value="{$form_data['batchlot']}"{/if}></dd>

                <dt><label for="pallet-counter">Pallet Counter:</label></dt>
                <dd><input id="pallet-counter" name="pallet-counter" type="text" {if $form_data['pallet-counter']}value="{$form_data['pallet-counter']}"{/if}></dd>

                <dt><label for="remember-data">Keep form input for next label</label></dt>
                <dd><input id="remember-data" name="remember-data" type="checkbox"{if $form_data['remember-data']=='on'} checked{/if}></dd>

                <dt><label for="increment-counter">Increment Pallet Counter</label></dt>
                <dd><input id="increment-counter" name="increment-counter" type="checkbox"{if $form_data['increment-counter']=='on'} checked{/if}></dd>

                <div class="form-actions">
                    <button id="search_print" type="submit">Output</button>
                    <button id="clear-form" type="button" name="clearform">Reset Form</button>
                </div>
            </dl>
        </div>
    </fieldset>
</form>
{/content_wrapper}