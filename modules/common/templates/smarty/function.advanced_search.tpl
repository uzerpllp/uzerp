{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{if $search neq ''}
	<div class="search" id="data_grid_search">
		<div class="search-summary float-right">
			<ul>
				<li><strong>Search Summary</strong></li>
				{assign var=search_summary value=$search->toString()}
				{if empty($search_summary)}
					<li><strong>All Records</strong></li>
				{else}
					<li>{$search_summary}</li>
				{/if}
				{if is_object($collection)}
					{assign var=policies value=$collection->addSystemRules()}
					{if !empty($policies.name)}
						<li><strong>Policies</strong></li>
						<li>{implode(',', $policies.name)}</li>
					{/if}
				{/if}
				<li>
					{if isset($total_records)}
						Search found <strong>{$total_records}</strong> {if $total_records==1}record{else}records{/if}
					{/if}
					{if isset($num_records) && isset($total_records) && $num_records<$total_records}
						: Limited to <strong>{$num_records}</strong> {if $num_records==1}record{else}records{/if}
					{/if}
				</li>
			</ul>
		</div>
		
		{if $function_advanced_search.action !== ''}
			{assign var=search_action value=$function_advanced_search.action}
		{else}
			{assign var=search_action value=$action}
		{/if}
		
		{form controller=$self.controller action=$search_action additional_data=$function_advanced_search.additional_data notags=true}
			{if $search->hasFields('advanced')}
				<fieldset id="advanced_holder">
					<input type="button" class="toggle-advanced-search" id="show_advanced_search" value="{if !$controller_data.advanced}+{else}-{/if}" />
				</fieldset>
			{/if}
			<fieldset id="hidden_search">
				{$search->toHTML('hidden')}
			</fieldset>
			<fieldset id="basic_search">
				<ul class="sortable">
					{$search->toHTML('basic')}
				</ul>
			</fieldset>
			<fieldset id="advanced_search" {if !$controller_data.advanced}style="display:none;"{/if}>
				<ul class="sortable">
					{$search->toHTML('advanced')}
				</ul>
			</fieldset>
			<fieldset id="submit_holder">
				<input type="submit" id="search_submit" value="Search" name="Search[search]"/>
				<input type="submit" id="search_clear" value="Clear" name="Search[clear]"/>
				{if $printaction!=''}
					<input type="submit" id="search_print" value="Output" name="Search[print]"/>
				{/if}
				<span class='float-left'>
					{if !empty($display_fields) || !empty($selected_fields)}
						<dt class="expand heading closed">Select Display Fields</dt>
						<dd style="display:none">
						<div width="100%">
							<div id="available_fields_container" class="eglets_container float-left">
								<h2>Available Fields</h2>
								<ul id="available_fields" class="available_fields connectedSortable">
									{foreach name=available_fields item=item key=index from=$display_fields}
										<li id="{$index}">{$item}</li>
									{foreachelse}
										<li class="none">None Currently Selected</li>
									{/foreach}
								</ul>
							</div>
							<div style="margin-left: 20px" id="selected_fields_container" class="eglets_container float-right">
								<h2>Currently Selected Fields</h2>
								<ul id="selected_fields" class="selected_fields connectedSortable">
									{foreach name=selected_fields item=item key=index from=$selected_fields}
										<li id="{$index}">{$item}</li>
									{foreachelse}
										<li class="none">None Currently Selected</li>
									{/foreach}
								</ul>
							</div>
						</div>
						</dd>
					{/if}
				</span>
			</fieldset>
		{/form}
	</div>
{/if}