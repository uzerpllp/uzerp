{**
*  @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
*  @license GPLv3 or later
*  @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
**}
{* $Revision: 1.9 $ *}
{content_wrapper}
    <div id="view_page" class="clearfix">
        <dl id="view_data_left">
            {with model=$report}
                <dt>Table/View Name</dt>
                <dd>{$report->tablename} &nbsp;</dd>
                <dt>Description</dt>
                <dd>{$report->description} &nbsp;</dd>
                <dt>Report Group</dt>
                <dd>{$report->report_group} &nbsp;</dd>
                <dt>Display fields</dt>
                <dd>{$display_fields} &nbsp;</dd>
                <dt>Break On Fields</dt>
                <dd>{$break_on_fields} &nbsp;</dd>
                <dt>Aggregate fields</dt>
                <dd>{$aggregate_fields} &nbsp;</dd>
                <dt>Search Fields</dt>
                <dd>{$search_fields} &nbsp;</dd>
                <dt>Filter Fields</dt>
                <dd>{$filter_fields} &nbsp;</dd>
                <dt>Report Definition</dt>
                <dd>{$report_definition} &nbsp;</dd>
            {/with}
        </dl>
        <dl id="view_data_bottom">
            {if !empty($roles)}
                {data_table}
                    <tr>
                        <th>
                            Published Roles
                        </th>
                    </tr>
                    {foreach item=role from=$roles}
                        <tr>
                            <td>
                                {$role}
                            </td>
                        </tr>
                    {/foreach}
                {/data_table}
            {/if}
        </dl>
    </div>
{/content_wrapper}
