{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<div id="pivot_table">
			<!-- left pane -->
			<div class="left-pane">
				{with model=$dataset legend="Dataset Details"}
					{assign var='title' value=$model->getIdentifierValue()}
					{assign var=fields value=$model->getDisplayFieldNames()}
					{assign var=identifier value=$model->getIdentifier()}
					{view_section heading="$title"}
						{foreach name=gridrow item=tag key=fieldname from=$fields}
							{if $fieldname<>$identifier}
								{view_data attribute=$fieldname label=$tag}
							{/if}
						{/foreach}
					{/view_section}
					{view_section heading="Fields"}
						<ul id="fields_list" class="connectedSortable field_list">
							{foreach item=field from=$dataset->fields}
								<li data-dataset-name="{$dataset->name}"
								    data-field-id="{$field->id}"
								    data-field-name="{$field->name}"
								    data-field-title="{$field->title}"
								    data-field-description="{$field->description}"
								    data-field-type="{$field->type}"
								    data-field-length="{$field->length}"
								    data-field-default-value="{$field->default_value}"
								    data-field-module-component-id="{$field->module_component_id}"
								    data-field-mandatory="{$field->mandatory}"
								    data-field-searchable="{$field->searchable}"
								    data-field-display-in-list="{$field->display_in_list}"
								    data-field-position="{$field->position}"
								    class="ui-state-default">
									<span style="width: 50%"><strong>{$field->title}</strong></span>
									{if !is_null($field->module_component_id)}
										<span style="width: 10%">Link</span>
									{else}
										<span style="width: 10%">{$dataset->getEnum('field_type', $field->type)}</span>
										<span style="width: 10%">{$field->length}</span>
									{/if}
									<span style="width: 10%">
										{if $field->mandatory=='t'}
											Mandatory
										{/if}
									</span>
									<span style="width: 10%">
										{if $field->searchable=='t'}
											Searchable
										{/if}
									</span>
									<span style="width: 10%">
										{if $field->display_in_list=='t'}
											Display
										{/if}
									</span>
								</li>
							{/foreach}
						</ul>
					{/view_section}
					<fieldset>
						<button id="new_field" class="filter clean-gray">New Field</button>
					</fieldset>
				{/with}
			</div>
			<!-- right pane -->
			<div class="right-pane">
				{form controller="datasets" action="save_field" }
					{with model=$dataset_fields legend="Dataset Field Details"}
						{view_section heading="Field Properties"}
							<div id="properties">
								{input type='hidden' attribute="id"}
								{input type='hidden' attribute="dataset_id" value=$dataset->id}
								{input type='hidden' attribute="dataset_name" value=$dataset->name}
								{select attribute="module_component_id" options=$links label="Links To"}
								{input type='hidden' attribute="name"}
								{input type='text' attribute="title" label="name"}
								{textarea attribute="description"}
								<span id='hide_on_link'>
									{select attribute="type" options=$field_types label="Field Type" nonone=true}
									{input type='text' attribute="length"}
									{input type='text' attribute="default_value"}
								</span>
								{input type='checkbox' attribute="mandatory"}
								{input type='checkbox' attribute="searchable"}
								{input type='checkbox' attribute="display_in_list"}
							</div>
						{/view_section}
						<fieldset>
							{submit tags=none}
							<button id="cancel_field" class="cancel clean-gray">Cancel</button>
							<button id="delete_field" class="delete clean-gray">Delete</button>
						</fieldset>
					{/with}
				{/form}
			</div>
		</div>
	</div>
{/content_wrapper}