{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$ModuleComponent}
 				{input type='hidden' attribute='id'}
				{view_data attribute="title"}
				{view_data attribute="description"}
				{view_data attribute="name" label='internal_name'}
				{view_data label="type" value=$internal_type|cat:' '|cat:$ModuleComponent->getEnum('type', $ModuleComponent->type)}
				{view_data label='Version' value=$version}
			{/with}
			{with model=$model_class}
				{view_data attribute=idField label='Key Field'}
				{view_data value=$model_class->getIdentifierFields()|implode:',' label='identifier Field'}
				{view_data label='Default Order' value=$model_class->getorderby()}
			{/with}
		</dl>
		<dl id="view_data_right">
			<ol class="permission_tree ui-sortable">
				<li class='placeholder'>
					<div>
						<span class="expand title closed">
							Belongs To
						</span>
					</div>
					<ol style="display: none">
						{foreach key=name item=belongsto from=$model_class->getBelongsTo()}
							<li id="" class='placeholder'>
								<div style="">
									<span class="title">
										{$belongsto.model} on {$name} field
									</span>
								</div>
							</li>
						{foreachelse}
							<li id="">
								Nothing defined
							</li>
						{/foreach}
					</ol>
				</li>
				<li class='placeholder'>
					<div>
						<span class="expand title closed">
							Has Many
						</span>
					</div>
					<ol style="display: none">
						{foreach key=name item=belongsto from=$model_class->getHasMany()}
							<li id="" class='placeholder'>
								<div style="">
									<span class="title">
										{$belongsto.do} {$name}
									</span>
								</div>
							</li>
						{foreachelse}
							<li id="">
								Nothing defined
							</li>
						{/foreach}
					</ol>
				</li>
				<li class='placeholder' data-id="{$ModuleComponent->id}">
					<div>
						<span id="data_access_policy" class="expand title closed">
							Data Access Policies
							<span>
								<a href="#" data-type="new">New</a>
							</span>
						</span>
					</div>
					<ol style="display: none">
						{foreach item=item from=$system_policies}
							<li id="" class='placeholder' data-id="{$item->id}">
								<div style="">
									<span class="title">
										<strong>Name</strong> {$item->name} <strong>Policy:</strong>
										{if $item->is_id_field}
											{$item->getFormatted('operator')} {$item->getValue()}
										{else}
											{$item->fieldname} {$item->getFormatted('operator')} {$item->value}
										{/if}
										<span>
											<a href="#" data-type="view">View</a> | <a href="#" data-type="edit">Edit</a> | <a href="#" data-type="delete">Delete</a>
										</span>
									</span>
								</div>
							</li>
						{foreachelse}
							<li id="">
								Nothing defined
							</li>
						{/foreach}
					</ol>
				</li>
			</ol>
		</dl>
		<div id="view_data_bottom">
			<ol class="permission_tree ui-sortable">
				<li class='placeholder'>
					<div>
						<span class="expand title closed">
							Fields
						</span>
					</div>
					<ol style="display: none">
			{data_table}
				{with model=$moduledefault legend="Module Defaults"}
					{heading_row}
						{heading_cell field="name"}
							Field Name
 						{/heading_cell}
 						<th>
 						</th>
 						<th>
 						</th>
						{heading_cell field="system_default_value"}
							System Default
						{/heading_cell}
						{heading_cell field="default_value"}
							Current Default
						{/heading_cell}
						{heading_cell field="display"}
							{$type|prettify}
						{/heading_cell}
					{/heading_row}
				{/with}
				{foreach item=model from=$models}
						{if $type=='display'}
							{assign var=display_fields value=$model->getDisplayFields()}
						{else}
							{assign var=display_fields value=$model->getInputFields()}
						{/if}
						{grid_row model=$model}
							<td colspan=6>
								<h3>
									{$model->getTitle()} - Tablename {$model->getViewName()}
								</h3>
							</td>
						{/grid_row}
						{assign var=belongs_to value=$model->belongsTo}
						{foreach item=field from=$fields}
							{assign var=field_name value=$field->name}
							{if (!$model->isHidden($field_name) || $field->system_override) && !isset($belongs_to.$field_name)}
								{grid_row model=$model}
									{grid_cell model=$model cell_num=2 field="name"}
										{$field_name|prettify}
									{/grid_cell}
									<td>
										{if $field->user_defaults_allowed}
											{link_to module="$module" controller="moduledefaults" action='edit' id=$field->id field_name=$field_name module_components_id=$ModuleComponent->id value='Change'}
										{/if}
									</td>
									<td>
										{if $field->user_defaults_allowed && $field->id<>''}
											{link_to module="$module" controller="moduledefaults" action='delete' id=$field->id module_components_id=$ModuleComponent->id value='Reset'}
										{/if}
									</td>
									<td>
										{if $field->has_default}
											{$field->system_default_value}
										{/if}
									</td>
									<td>
										{assign var=field value=$model->getField($field_name)}
										{if $field->has_default}
											{if isset($model->belongsToField.$field_name) || $model->isEnum($field_name)}
												{$moduledefault->getValue($model, $field)}
											{else}
												{$field->display_default_value}
											{/if}
										{/if}
									</td>
									<td>
										{if isset($display_fields.$field_name)}
											Yes
										{else}
											No
										{/if}
									</td>
								{/grid_row}
							{/if}
						{foreachelse}
							<td colspan=6>
								No matching records found
							</td>
						{/foreach}
						{grid_row model=$model}
							<td colspan=6>
								<hr>
							</td>
						{/grid_row}
				{/foreach}
			{/data_table}
					</ol>
				</li>
			</ol>
		</div>
	</div>
{/content_wrapper}