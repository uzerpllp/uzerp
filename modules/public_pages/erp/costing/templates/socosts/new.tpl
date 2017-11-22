{**
* @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
* @license GPLv3 or later
* @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 **}
{* $Revision: $ *}
{content_wrapper}
    {form controller="socosts" action="save"}
        {with model=$models.SOCost legend="Product Cost"}
            {input type='hidden' attribute='id'}
            {include file='elements/auditfields.tpl'}
            {if $model->isLoaded()}
                {view_data label='SO Product' attribute='product_header_id'}
            {else}
                {select label='SO Product' attribute="product_header_id" data=$soprods }
            {/if}
            {input type='text' label='Material' attribute='mat' }
            {input type='text' label='Labour' attribute='lab' }
            {input type='text' label='Outside Ops' attribute='osc' }
            {input type='text' label='Overhead' attribute='ohd' }
            {input type='text' label='Time' attribute='time' }
            {select label='Time Period' attribute='time_period' }
            {submit}
                {submit value='Save and Add Another' name='saveadd' id='saveadd'}
        {/with}
    {/form}
    {include file='elements/cancelForm.tpl' action='index'}
{/content_wrapper}
