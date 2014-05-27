/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * system_admin.js
 * 
 * $Revision: 1.8 $
 *
 */

$(document).ready(function(){
	
	// system_admin -> moduleobjects -> new

	$('#ModuleObject_name', '#system_admin-moduleobjects-new').on('change', function() {
		
		var $self = $(this);
		
		$('#ModuleObject_location').uz_ajax({
			data:{
				module		: 'system_admin',
				controller	: 'moduleobjects',
				action		: 'getPathName',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
		$('#moduleobject_description').val($self.prettify());
		
	});
	
	
	// system_admin -> modulecomponents -> new

	$('#system_admin-modulecomponents-view').on('click', '.expand.title', function() {
		
		var $self		= $(this),
			$children	= $self.closest('li').children('ol');
		
		$self.removeClass('open closed');
		
		if ($children.is(':visible')) {
			$self.addClass('closed');
			$children.hide();
		} else {
			$self.addClass('open');
			$children.show();
		}
		
	});
	
	$(".permission_tree", '#system_admin-modulecomponents-view').on('mouseover', 'div', function () {
		
		$(this)
			.find('span > span')
				.stop(true)
				.animate({'opacity': 1});
			
	});
	
	$(".permission_tree", '#system_admin-modulecomponents-view').on('mouseout', 'div', function () {
		
		$(this)
			.find('span > span')
				.stop(true)
				.animate({'opacity': 0});
			
	});
	
	$('.permission_tree', '#system_admin-modulecomponents-view').on('click', 'span > span a', function(event) {
		
		event.preventDefault();
		
		var $this		= $(this),
			data_id		= $this.closest('li').data('id'),
			calledby	= $this.parents('.content_wrapper').attr('id'),
			url			= '/?module=system_admin&controller=systempolicys&action=',
			element		= 'included_file';
		
		$('#flash').empty();
		
		switch ($this.data('type'))
		{
		
			case 'new':
				
				formDialog({
					title		: 'New System Policy',
					calledby	: calledby,
					id			: element,
					refresh		: true,
					url			: url+'new&module_components_id='+data_id,
					data		: '',
					type		: 'add',
					height		: 550,
					width		: 550,
					resizable	: true,
					callback	: function() {
					
					}
				});

				break;
				
			case 'edit':
				
				formDialog({
					title		: 'Edit System Policy',
					calledby	: calledby,
					id			: element,
					refresh		: true,
					url			: url+'edit&id='+data_id,
					data		: '',
					type		: 'edit',
					height		: 550,
					width		: 550,
					resizable	: true,
					callback	: function() {
					
					}
				});
				
				break;
			
			case 'view':
				
				window.location.href = url+'view&id='+data_id;
				
				break;
			
			case 'delete':
				
				if (confirm("Delete this policy?")) {
					
					$.uz_ajax({
						data: {
							module		: 'system_admin',
							controller	: 'systempolicys',
							action		: 'delete',
							ajax		: '',
							id			: data_id
						},
						success: function(data) {
							
							if (data.success === true) {

								window.location.href=window.location.href;
								
								$('#flash').empty().append("<ul id='messages'><li>Policy deleted successfully</li></ul>");
							
							} else {
								
								$('#flash').empty().append('<ul id="errors"><li>Failed to delete policy</li></ul>');
							
							}
							
						}
					});
					
				}
				
				break;
				
		}
		
	});
	
	// system_admin-systemobjectpolicys-new
	
	$('#SystemObjectPolicy_module_components_id').live('change', function() {
		
		$('#SystemObjectPolicy_fieldname').uz_ajax({
			data: {
				module					: 'system_admin',
				controller				: 'systemobjectpolicys',
				action					: 'get_fields',
				module_components_id	: $('#SystemObjectPolicy_module_components_id').val(),
				ajax					: ''
			}
		});
		
	});
	
	$('#SystemObjectPolicy_fieldname').live('change', function() {
		
		$.uz_ajax({
			target : [{ 
						element: '#SystemObjectPolicy_operator',
						field : 'operators'
					  },
					  { 
						element: '#input_value',
						field : 'input_value'
					  }
			],
			data: {
				module					: 'system_admin',
				controller				: 'systemobjectpolicys',
				action					: 'get_values',
				field_name				: $(this).val(),
				module_components_id	: $('#SystemObjectPolicy_module_components_id').val(),
				value					: $('#SystemObjectPolicy_value').val(),
				ajax					: ''
			}
		});
		
	});
	
	// system_admin-systemobjectpolicys-view
	
	$(".edit-line a, .add_policy_permission_related a", "#system_admin-systemobjectpolicys-view").live('click', function(event){
		
		event.preventDefault();
		
		var $self = $(this);
		
		if ($self.parent('li').hasClass('add_policy_permission_related')) {
			var title='Add System Policy Permission';
			var type='add';
		} else {
			var title='Edit System Policy Permission';
			var type='edit';
		}

		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'systempolicycontrollists',
			url			: $self.attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true,
			callback	: function() {
			
			}
		});
		
	});
	
	// system_admin-systempolicyaccesslists-new
	
	$("#SystemPolicyAccessList_access_type", "#system_admin-systempolicyaccesslists-new").live('click', function(event){
		
		$('#SystemPolicyAccessList_access_object_id').uz_ajax({
			data: {
				module					: 'system_admin',
				controller				: 'systempolicyaccesslists',
				action					: 'get_values',
				access_type				: $(this).val(),
				ajax					: ''
			}
		});
		
	});
	
	// system_admin -> setup -> index
	
	$('#sidebar_Stages', '#system_admin-setup-index').on('click', '.setup_stage', function(event) {
		
		alert('sidebar');
		
		event.preventDefault();
		
		var $self = $(this);
		
		// remove all existing current classes
		$('.current', '#sidebar_Stages').removeClass('current');
		
		// add current class to this link
		$self.parent('li').addClass('current');
		
		// load the page with the desired stage
		$('#included_file').uz_ajax({
			url: $self.attr('href')
		});
		
	});
	
	
	// system_admin -> permissions -> index
	
	// update the expand / contract arrows
	update_permission_tree();
	
	$('.permission_tree', '#system_admin-permissions-index').nestedSortable({
		forcePlaceholderSize: true,
		handle: 'span',
		helper:	'clone',
		items: 'li',
		maxLevels: 0,
		opacity: .6,
		placeholder: 'placeholder',
		revert: 250,
		tabSize: 25,
		tolerance: 'pointer',
		toleranceElement: '> div',
		cancel: 'span > span',
		update: function(event, ui) {
		
			// update the tree, enabling / disabling child arrows 
			update_permission_tree();
			
			var $item		= $(ui.item),
				$parent		= $item.parent().closest('li', '.permission_tree'),
				parent_id	= '';
			
			if ($parent.length) {
				parent_id = $parent.data('id');
			}
			
			// update item
			$.uz_ajax({
				url: '/?module=system_admin&controller=permissions&action=update',
				data: {
					permission_id	: $item.data('id'),
					parent_id		: parent_id,
					position		: $item.index() + 1,
					type			: $item.data('type')
				},
				type: 'POST'
			});
			
			/// ATTN: above, need a succss callback
			
		}
	});
	
	$('#system_admin-permissions-index').on('click', '.new_permission > ul li', function() {
		
		var $self = $(this);
		
		// remove the current class from the existing li elements
		$('.new_permission > ul li.current').removeClass('current');
		
		// add the current class to the clicked element
		$self.addClass('current');
		
		// hide all the containers
		$('.permission_container > div').hide();
		
		// show the one we select by index
		$('.permission_container > div').eq($self.index()).show();
		
		
	});
	
	$('.new_permission', '#system_admin-permissions-index').on('click', 'button', function(event) {
		
		event.preventDefault();
		
		var $self = $(this);
		
		switch ($self.data('action')) {
		
			case 'cancel':
				reset_add_form();
				break;
				
			case 'save':
				
				$.uz_ajax({
					url: '/?module=system_admin&controller=permissions&action=save&',
					data: $self.closest('form').serialize(),
					type: 'POST',
					success: function(data) {
						
						$('#main_without_sidebar').scrollTo( { top:0, left:0 }, 1000 );
	
						if (data.success == true) {
	
							$('#flash').empty().append("<ul id='messages'>" + data.messages + "</ul>");
	
							reset_add_form();
							
							fetch_permission_tree();
	
						} else {
							
							if (data.errors !== undefined || data.errors !== '') {
								$('#flash').empty().append('<ul id="errors">' + data.errors + '</ul>');
							}
							
						}
						
					}
					
				});
				
				break;
				
		}
		
	});

	$('.new_permission', '#system_admin-permissions-index').on('change', '.module, .controller, .action', function() {
	
		var $self		= $(this),
			$module		= $('.module'),
			$controller	= $('.controller'),
			$action		= $('.action'),
			$type_span	= $('.type');

		update_type_label();
		
		switch($self.data('type')) {

			case 'module':

				$.uz_ajax({
					target: {
						element	: '.controller',
						field	: 'controller'
					},
					data:{
						module			: 'system_admin',
						controller		: 'permissions',
						action			: 'get_controller_list',
						module_id		: $module.val(),
						ajax			: ''
					}
				});

				break;

			case 'controller':
				
				if ($('.controller').val()) {
					
					$.uz_ajax({
						target: {
							element	: '.action',
							field	: 'action'
						},
						data:{
							module			: 'system_admin',
							controller		: 'permissions',
							action			: 'get_action_list',
							controller_id	: $controller.val(),
							ajax			: ''
						}
					});
					
				}
				
				break;

		}

	});
			
	$('#system_admin-permissions-index').on('click', '.expand', function() {
		
		var $self		= $(this),
			$children	= $self.closest('li').children('ol');
		
		$self.removeClass('open closed');
		
		if ($children.is(':visible')) {
			$self.addClass('closed');
			$children.hide();
		} else {
			$self.addClass('open');
			$children.show();
		}
		
	});
	
	$(".permission_tree", '#system_admin-permissions-index').on('mouseover', 'div', function () {
			
		$(this)
			.find('span > span')
				.stop(true)
				.animate({'opacity': 1});
			
	});
	
	$(".permission_tree", '#system_admin-permissions-index').on('mouseout', 'div', function () {
		
		$(this)
			.find('span > span')
				.stop(true)
				.animate({'opacity': 0});
			
	});
	
	$('.permission_tree', '#system_admin-permissions-index').on('click', 'span > span', function(event) {
		
		event.stopPropagation();
		event.preventDefault();
		
	});
	
	$('.permission_tree', '#system_admin-permissions-index').on('click', 'span > span a', function(event) {
		
		event.preventDefault();
		
		var $this			= $(this),
			permission_id	= $this.closest('li').data('id');
		
		$('#flash').empty();
		
		switch ($this.data('type'))
		{
		
			case 'edit':
				
				$('#main_without_sidebar').scrollTo( { top:0, left:0 }, 1000 );
				
				$('.new_permission').html('').uz_ajax({
					data: {
						module		: 'system_admin',
						controller	: 'permissions',
						action		: 'edit',
						id			: permission_id
					}
				});
				
				break;
			
			case 'delete':
				
				if (confirm("Delete this permission?")) {
					
					$.uz_ajax({
						data: {
							module		: 'system_admin',
							controller	: 'permissions',
							action		: 'delete',
							id			: permission_id
						},
						success: function(data) {
							
							if (data.success === true) {
								fetch_permission_tree();
								$('#flash').empty().append("<ul id='messages'><li>Permission deleted successfully, updating permission tree</li></ul>");
							} else {
								$('#flash').empty().append('<ul id="errors"><li>Failed to delete permission</li></ul>');
							}
							
							
						}
					});
					
				}
				
				break;
				
			case 'add-child':
				
				$('.new_permission').uz_ajax({
					data: {
						module		: 'system_admin',
						controller	: 'permissions',
						action		: 'add',
						parent_id	: permission_id
					}
				});
		
		}
		
	});
	
});

function fetch_permission_tree() {
	
	var open_branches = [];
	
	// store the id's of the permission branches that are open
	$('.permission_tree ol:visible').each(function() {
		open_branches.push($(this).closest('li').data('id'));
	});
	
	// fetch the new permission tree
	$('.permission_tree').uz_ajax({
		data:{
			module			: 'system_admin',
			controller		: 'permissions',
			action			: 'tree',
			ajax			: ''
		},
		complete: function() {
			
			// attempt to re-open the previously open branches
			for (var i = 0; i < open_branches.length; i++) {
				$('#permission_' + open_branches[i]).find('> ol').show();
			}
			
			// update the permission tree handles
			update_permission_tree();
			
		}
	});
	
}

function update_permission_tree() {
	
	$('.expand', '.permission_tree').removeClass('open closed');
	
	$('li', '.permission_tree').each(function() {
		
		var $self		= $(this),
			$span		= $self.find(' > div .title');
			$children	= $self.children('ol');

		if ($children.length && $children.find('*').length) {
			
			$span.addClass('expand');
			
			if ($children.is(':visible')) {
				$span.addClass('open');
			} else {
				$span.addClass('closed');
			}
			
		} else {
			$span.removeClass('expand');
		}
		
	});
	
}

function update_type_label() {

	var $type_span = $('.type');
	
	if ($('.module').val() !== '') {
		$type_span.html('Module');
	}
	
	if ($('.controller').val() !== '') {
		$type_span.html('Controller');
	}
	
	if ($('.action').val() !== '') {
		$type_span.html('Action');
	}
	
}

function reset_add_form() {
	
	$('.new_permission')
		.empty()
		.uz_ajax({
			data: {
				module:		'system_admin',
				controller: 'permissions',
				action:		'new'
			}
		});
	
}