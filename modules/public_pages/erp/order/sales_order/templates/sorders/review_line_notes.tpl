{** 
 *  (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *  Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper title=$page_title}
    {if count($sorderlines) > 0}
        {data_table}
            <thead>
                <tr>
                    <th class='right'>Line #</th>
                    <th align='left'>Description</th>
                    <th class='right'>Unit price</th>
                    <th align='left'>Note</th>
                </tr>
            </thead>
            {foreach name=datagrid item=line from=$sorderlines}
                {assign var=line_number value=$line->line_number}
                {assign var=id value=$line->id}
                <tr data-line-number="{$line_number}">
                    <td align='right'>{$line->line_number}</td>
                    <td align='left'>
                        {if !is_null($line->stitem_id)}
                            {assign var=stitem_id value=$line->stitem_id}
                            {assign var=description value=$line->description}
                            {link_to module="manufacturing" controller="stitems" action="view" id="$stitem_id" value="$description"}
                        {else}
                            {$line->description}
                        {/if}
                    </td>
                    <td align='right'>{$line->price|string_format:"%.2f"}</td>
                    <td align='left'>{$line->note}</td>
                </tr>

            {/foreach}
        {/data_table}
    {else}
        <p>This order has no lines with notes</p>
    {/if}
{/content_wrapper}