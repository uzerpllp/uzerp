/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * hr.js
 * 
 * $Revision: 1.26 $
 * 
 */

$(document).ready(function() {
	
	/* hr -> employees -> new */
	
	$("#Employee_person_id", "#hr-employees-new").live('change',function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[{
					element	: "#Employee_title",
					field	: "title"
				},
				{
					element	: "#Employee_firstname",
					field	: "firstname"
				},
				{
					element	: "#Employee_middlename",
					field	: "middlename"
				},
				{
					element	: "#Employee_surname",
					field	: "surname"
				},
				{
					element	: "#Employee_suffix",
					field	: "suffix"
				},
				{
					element	: "#Employee_jobtitle",
					field	: "jobtitle"
				},
				{
					element	: "#Employee_department",
					field	: "department"
				},
				{
					element	: "#Employee_reports_to",
					action	: "selected",
					field	: "reports_to"
			}],
			data:{
				module		: 'hr',
				controller	: 'employees',
				action		: 'getPersonData',
				person_id	: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* hr -> employees -> new/edit */
	
	$("#Employee_department", "#hr-employees-new, #hr-employees-edit").change(function(){
		
		if ($("#Employee_mfdept_id").val()!='' && $("#Employee_department").val()!=$('option:selected', "#Employee_mfdept_id").text())
		{
			$("#Employee_mfdept_id").val('');
		}
				
	});
			
	$("#Employee_mfdept_id", "#hr-employees-new, #hr-employees-edit").change(function(){
		
		if ($("#Employee_mfdept_id").val()=='')
		{
			$('#Employee_department').val('');
			$('#Employee_department').focus();
		}
		else 
		{
			$('#Employee_department').val($('option:selected', "#Employee_mfdept_id").text());
		}
		
	});
	
	/* hr -> employees -> edit_personal */
	
	$("#Employee_address_id", "#hr-employees-edit_personal").live('change',function(){
		if ($('#Employee_address_id').val() == '')
		{
			$("#address").show();
			$("#Address_id").val('');
		}
		else
		{
			$("#address").hide();
			$("#Address_id").val($('#Employee_address_id').val());
		}
	});
	
	/* hr -> employees -> edit_work */
	
	$("#Address_fulladdress", "#hr-employees-edit_work").live('change',function(){
		if ($('#Address_fulladdress').val() == '')
		{
			$("#address").show();
			$("#Address_id").val('');
		}
		else
		{
			$("#address").hide();
			$("#Address_id").val($('#Address_fulladdress').val());
		}
	});
	
	/* hr -> employees -> make_payment */
	
	$("#CBTransaction_cb_account_id", "#hr-employees-make_payment").live('change',function(){
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAllowedCurrencies',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#CBTransaction_currency_id", "#hr-employees-make_payment").live("change", function(){
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'hr',
				controller		: 'employees',
				action			: 'getCurrencyRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				currency_id		: $('#CBTransaction_currency_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_rate", "#hr-employees-make_payment").live("change", function(){
		if ($(this).val()=='') {
			$("#conversion_rate").hide();
		}
		else {
			$("#conversion_rate").show();
		}
		
	});
	
	/* hr -> expense -> new */

	$("#Expense_project_id", "#hr-expenses-new").on('change',function(){
		
		$.uz_ajax({
			target:{
					element	: "#Expense_task_id",
					field	: 'task_id'
		    	},
			data:{
				module		: 'hr',
				controller	: 'expenses',
				action		: 'getTaskList',
				project_id	: $('#Expense_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	/* hr -> expense -> view */
	
	$(".edit-line a, .add_lines_related a").live('click', function(event){
		
		event.preventDefault();
		
		var $self = $(this);
		
		if ($self.parent('li').hasClass('add_lines_related')) {
			var title='Add Expense Line';
			var type='add';
		} else {
			var title='Edit Expense Line';
			var type='edit';
		}

		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'expenselines',
			url			: $self.attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true
		});
		
	});
	
	/* hr -> expenses -> make_payment */
	
	$("#CBTransaction_cb_account_id", "#hr-expenses-make_payment").live('change',function(){
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAllowedCurrencies',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#CBTransaction_currency_id", "#hr-employees-make_payment").live("change", function(){
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'hr',
				controller		: 'expenses',
				action			: 'getCurrencyRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				currency_id		: $('#CBTransaction_currency_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_rate", "#hr-employees-make_payment").live("change", function(){
		if ($(this).val()=='') {
			$("#conversion_rate").hide();
		}
		else {
			$("#conversion_rate").show();
		}
		
	});
	
	/* hr -> expenselines -> new */
	
	$("select, input", "#hr-expenselines-new").live('change',function() {
		
		var $self	= $(this),
			field	= $self.data('field');

		
		switch (field) {
			case "qty":
			case "purchase_price":
				$self.val(roundNumber($(this).val(), 2)); 
				calcValue('#ExpenseLine_gross_value', $('#ExpenseLine_qty').val(), $('#ExpenseLine_purchase_price').val());
				$('#ExpenseLine_net_value').val($('#ExpenseLine_gross_value').val());
				$('#ExpenseLine_tax_value').val(0);
				break;
				
			case "net_value":
				$self.val(roundNumber($(this).val(), 2));
				addValues('#ExpenseLine_net_value', '#ExpenseLine_tax_value', '#ExpenseLine_gross_value');
				debug.debug($('#ExpenseLine_qty'));
				if ($('#ExpenseLine_qty').val()=='' || $('#ExpenseLine_qty').val()==0) {
					$('#ExpenseLine_qty').val(1);
					$('#ExpenseLine_purchase_price').val(this.value);
				}
				break;
				
			case "tax_value":
				$self.val(roundNumber($(this).val(), 2));
				if ($('#ExpenseLine_awaitingAuth').val()) {
					addValues('#ExpenseLine_net_value', '#ExpenseLine_tax_value', '#ExpenseLine_gross_value');
				}
				else {
					$('#ExpenseLine_net_value').val(roundNumber($('#ExpenseLine_gross_value').val()-$('#ExpenseLine_tax_value').val(), 2));
				}
				break;
				
			case "gross_value":
				$self.val(roundNumber($(this).val(), 2));
				$('#ExpenseLine_net_value').val(roundNumber($('#ExpenseLine_gross_value').val()-$('#ExpenseLine_tax_value').val(), 2));
				break;
				
			case "glaccount_id":
				
				$('#ExpenseLine_glcentre_id').uz_ajax({
					data:{
						module		: 'hr',
						controller	: 'expenselines',
						action		: 'getCentres',
						glaccount_id: $self.val(),
						ajax		: ''
					}
				});
				
		}
		
	});

	/* hr -> employees -> allocate */
	
	// ATTENTION: JQI: this needs testing
	$("tr input:checkbox", "#hr-employees-allocate").live("click", function() {
		var row = $(this).parents('tr').attr('rel');
		var value = $(this).parents('tr').find('#os_value'+row);
		var target = $(this).parents('table').find('#allocated_total');
		$(this).setInvoiceTotal(value,target);
	});
	
	/* hr -> employeerates -> new */
	
	$("#EmployeeRate_payment_type_id", "#hr-employeerates-new").live('change',function(){
		
		$.uz_ajax({
			target:[{
					element	: "#EmployeeRate_start_date",
					field	: 'start_date'
		    	},
		    	{
					element	: "#current_rates",
					field	: 'current_rates'
		    	}
			],
			data:{
				module			: 'hr',
				controller		: 'employeerates',
				action			: 'getRateTypeData',
				employee_id		: $('#EmployeeRate_employee_id').val(),
				payment_type_id	: $(this).val(),
				ajax			: ''
			}
		});
		
	});
	
	/* hr -> employeepayhistory -> new */
	
	/*$('#hour_types input:visible:enabled:first').focus();*/

	$("#EmployeePayHistory_employee_pay_periods_id", "#hr-employeepayhistorys-new").live('change',function(){

		$.uz_ajax({
			target:[
				{
					element	: "#EmployeePayHistory_employee_id",
					//field	: 'employee_id'
				}
			],
			data:{
				module					: 'hr',
				controller				: 'employeepayhistorys',
				action					: 'getEmployeesForPeriod',
				employee_pay_periods_id	: $('#EmployeePayHistory_employee_pay_periods_id').val(),
				ajax					: ''
			}
		});
		
		//$('#hour_types input:visible:enabled:first').focus();
		
	});

	$("#EmployeePayHistory_employee_pay_periods_id, #EmployeePayHistory_employee_id", "#hr-employeepayhistorys-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		    	{
					element	: "#hour_types",
					field	: 'hour_types'
		    	}
			],
			data:{
				module					: 'hr',
				controller				: 'employeepayhistorys',
				action					: 'getPayPeriodData',
				employee_id				: $('#EmployeePayHistory_employee_id').val(),
				employee_pay_periods_id	: $('#EmployeePayHistory_employee_pay_periods_id').val(),
				ajax					: ''
			}
		});
		
		$('#hour_types input:visible:enabled:first').focus();
		
	});
	
	$("#EmployeePayHistory_employee_id", "#hr-employeepayhistorys-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		    	{
					element	: "#current_rates",
					field	: 'current_rates'
		    	}
			],
			data:{
				module				: 'hr',
				controller			: 'employeepayhistorys',
				action				: 'getPayHistoryData',
				employee_id			: $('#EmployeePayHistory_employee_id').val(),
				period_start_date	: $('#EmployeePayHistory_$period_start_date').val(),
				ajax				: ''
			}
		});
		
	});
	
	$(".pay_units, .pay_rate", "#hr-employeepayhistorys-new").live('change',function(){
		
		var row_number	= $(this).data('row-number'),
			val1		= $('#EmployeePayHistory_pay_units'+row_number).val(),
			val2		= $('#EmployeePayHistory_pay_rate'+row_number).val();
		
		var quantity	= isNaN(parseFloat(val1))?0:parseFloat(val1),
			price		= isNaN(parseFloat(val2))?0:parseFloat(val2),
			value		= roundNumber(quantity * price, 2);
		
		$('#EmployeePayHistory_pay_total'+row_number).html(value);
		
	});
	
	/* hr -> employeepayperiods -> new */
	
	$("#EmployeePayPeriod_pay_basis, #EmployeePayPeriod_period_start_date", "#hr-employeepayperiods-new").live('change',function(){
		
		$('#EmployeePayHistory_period_end_date').uz_ajax({
			data:{
				module				: 'hr',
				controller			: 'employeepayperiods',
				action				: 'getPeriodEndDate',
				period_start_date	: $('#EmployeePayPeriod_period_start_date').val(),
				pay_basis			: $('#EmployeePayPeriod_pay_basis').val(),
				ajax				: ''
			}
		});
		
	});
	
	/* hr -> holidayrequests -> new */
	
	$("#Holidayrequest_employee_id", "#hr-holidayrequests-new").live('change',function(){

		$('#Holidayrequest_days_left').uz_ajax({
			async: false,
			data:{
				module		: 'hr',
				controller	: 'holidayrequests',
				action		: 'getDaysLeft',
				employee_id	: $('#Holidayrequest_employee_id').val(),
				ajax		: ''
			}
		});
		
		var days_left		= isNaN(parseFloat($('#Holidayrequest_days_left').html()))?0:parseFloat($('#Holidayrequest_days_left').html()),
			num_days		= isNaN(parseFloat($('#Holidayrequest_num_days').val()))?0:parseFloat($('#Holidayrequest_num_days').val()),
			new_days_left	= roundNumber(days_left - num_days, 1);
		
		$('#Holidayrequest_new_days_left').html(new_days_left);
	
	});
	
	$("#Holidayrequest_start_date, #Holidayrequest_end_date", "#hr-holidayrequests-new").live('change',function(){

		if ($.datepicker.formatDate('yy-mm-dd', $.datepicker.parseDate( "dd/mm/yy", $("#Holidayrequest_start_date").val())) < $("#Holidayrequest_today").val()) {
			alert("Warning:\n\nStart Date is before today");
		}
		
		$('#Holidayrequest_num_days').uz_ajax({
			async: false,
			data:{
				module		: 'hr',
				controller	: 'holidayrequests',
				action		: 'getNumberDays',
				start_date	: $('#Holidayrequest_start_date').val(),
				end_date	: $('#Holidayrequest_end_date').val(),
				all_day		: $('.all_day:checked').val(),
				ajax		: ''
			}
		});
		
		var days_left		= isNaN(parseFloat($('#Holidayrequest_days_left').html()))?0:parseFloat($('#Holidayrequest_days_left').html()),
			num_days		= isNaN(parseFloat($('#Holidayrequest_num_days').val()))?0:parseFloat($('#Holidayrequest_num_days').val()),
			new_days_left	= roundNumber(days_left - num_days, 1);
		
		$('#Holidayrequest_new_days_left').html(new_days_left);
	
	});
	
	$("#Holidayrequest_num_days", "#hr-holidayrequests-new").live('change',function(){

		var days_left		= isNaN(parseFloat($('#Holidayrequest_days_left').html()))?0:parseFloat($('#Holidayrequest_days_left').html()),
			num_days		= isNaN(parseFloat($('#Holidayrequest_num_days').val()))?0:parseFloat($('#Holidayrequest_num_days').val()),
			new_days_left	= roundNumber(days_left - num_days, 1);
		
		$('#Holidayrequest_new_days_left').html(new_days_left);
	
	});
	
	$(".all_day", "#hr-holidayrequests-new").live('change',function(){
		
		var days = isNaN(parseFloat($('#Holidayrequest_num_days').val()))?0:parseFloat($('#Holidayrequest_num_days').val());

		if ($(this).val()=='t') {
			var	value	= days * 2;
		}
		else {
			var	value	= days / 2;
		}
		
		value = value.toFixed(1);
		
		$('#Holidayrequest_num_days').val(value);
		
		var days_left		= isNaN(parseFloat($('#Holidayrequest_days_left').html()))?0:parseFloat($('#Holidayrequest_days_left').html()),
			num_days		= isNaN(parseFloat($('#Holidayrequest_num_days').val()))?0:parseFloat($('#Holidayrequest_num_days').val()),
			new_days_left	= roundNumber(days_left - num_days, 1);
			
		$('#Holidayrequest_new_days_left').html(new_days_left);
		
	});
	
	/* hr -> hours -> hours_new */
	
	$("#Hour_opportunity_id", "#hr-hours-hours_new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#Hour_project_id",
				field	: 'project_id'
		    },
			data:{
				module			: 'hr',
				controller		: 'Hours',
				action			: 'getProjectList',
				opportunity_id	: $('#Hour_opportunity_id').val(),
				ajax			: ''
			}
		});
		
		
	});
	
	$("#Hour_project_id", "#hr-hours-hours_new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#Hour_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'hr',
				controller	: 'Hours',
				action		: 'getTaskList',
				project_id	: $('#Hour_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#hour_start_time, #Hour_owner", "#hr-hours-hours_new").live('change',function(){
		
		$('#hours_summary').uz_ajax({
			data:{
				module		: 'hr',
				controller	: 'hours',
				action		: 'getHours',
				owner		: $('#Hour_owner').val(),
				start_date	: $('#hour_start_time').val(),
				ajax		: ''
			}
		});
		
	});

	// Holiday Requests Calendar
	
	function hr_calendar() {
		
		var $calendar = $('#calendar')
			,saveButton = {
				Save: function() {
					form_data	= $('#add_event').find('#save_form').serialize();
					url			= $('#add_event').find('#save_form').attr('action');
				
					$.ajax({
						type:'POST',
						url: url + '&ajax=&dialog=',
						data: form_data,
						dataType: "html",
						success: function(data) {
							if (data !== undefined && data !== null) {
							
								if (data.substr(0, 1) == '{') {
									var jsondata = JSON.parse(data);
								
									if (jsondata.status==true) {
										$calendar.data('event_status', true);											
										$('#add_event').dialog('close');
									
									}
								
								} else {
									// if data is empty then close the dialog, otherwise
									// probably had an error so just need to refresh the current dialog
									// with the returned html data
									if (data=="") {
										$('#add_event').dialog('close');
									}
									else {
										$('#add_event').html(data);
									}
								}
							
							}
						}
					})
				}
			}
			,deleteButton = {
					Delete: function() {
						var $_GET		= getQueryParams($('#add_event').find('#save_form').attr('action'));
					
						delete $_GET.pid;
						
						$_GET.action	= 'delete';
						url				= '?' + makeQueryString($_GET);
						
						form_data	= $('#add_event').find('#save_form').serialize();
						
						$.ajax({
							type:'POST',
							url: url + 'ajax=&dialog=',
							data: form_data,
							dataType: "html",
							success: function(data) {
								if (data !== undefined && data !== null) {
								
									if (data.substr(0, 1) == '{') {
										var jsondata = JSON.parse(data);
									
										if (jsondata.status==true) {
											$calendar.data('event_status', true);											
											$('#add_event').dialog('close');
										
										}
									
									} else {
										// if data is empty then close the dialog, otherwise
										// probably had an error so just need to refresh the current dialog
										// with the returned html data
										if (data=="") {
											$('#add_event').dialog('close');
										}
										else {
											$('#add_event').html(data);
										}
									}
								
								}
							}
						})
					}
			}
			,closeButton = {
					Close: function() {
						$('#add_event').dialog('close');
					}
			};
	
		$calendar.fullCalendar({
			header: {
				left	: 'prev,next today',
				center	: 'title',
				right	: ''
			},
			allDaySlot		: true,
			defaultView		: 'month',
			editable		: false,
			weekends		: false,
//			minTime			: 8,
//			maxTime			: 18,
			selectable		: true,
			selectHelper	: true,
//			slotMinutes		: 15,
			events			: "/?module=hr&controller=holidayrequests&action=getHolidays",
			
		    eventRender: function(event, element) {
		    	// Rules for drop down menu
		    	// 1) Only editable events are enabled (status authorised or awaiting authorisation)
		    	// 2) A user can cancel their own request if the request is awaiting authorisation
		    	// 3) An authoriser can cancel, authorise or decline a request awaiting authorisation
		    	// 4) Only an authoriser can cancel an authorised request
		    	if (event.editable) {
					
			    	element.attr('id', event.id);
			    	
			    	var items = {};
			    	
			    	if ((event.status=='W' || event.authoriser && event.status=='A')) {
			            items['cancel_request'] = {name: 'Cancel Request', icon: 'cancel_request'};
			    	}
			    	if (event.authoriser && event.status=='W') {
			            items['authorise_request'] = {name: 'Authorise Request', icon: 'authorise_request'};
			            items['decline_request'] = {name: 'Decline Request', icon: 'decline_request'};
			    	}
			    	$.contextMenu({

			            selector: ('#'+event.id),//note the selector this will apply context to all events 
			            trigger: 'right',
			            callback: function(key, options) {

			                action = key;
			                
			                switch(key)
			                {
			                case 'cancel_request':
								var action='delete';

			                case 'authorise_request':
								
								$.ajax({
									type:'POST',
									url: '/?module=hr&controller=holidayrequests&action='+action+'&id='+event.id+'&ajax=&dialog=',
									dataType: "html",
									success: function(data) {
										if (data !== undefined && data !== null) {
										
											if (data.substr(0, 1) == '{') {
												var jsondata = JSON.parse(data);
											
												if (jsondata.status==true) {
													$calendar.data('event_status', true);											
													$('#add_event').dialog('close');
												
												}
											
											} else {
												// if data is empty then close the dialog, otherwise
												// probably had an error so just need to refresh the current dialog
												// with the returned html data
												if (data=="") {
													$('#add_event').dialog('close');
												}
												else {
													$('#add_event').html(data);
												}
											}
										
										}
									}
								});

			                  break;
			                case 'decline_request':
								var action='decline_request';

								$('#add_event').uz_ajax({
									url			: '/?module=hr&controller=holidayrequests&action='+action+'&id='+event.id + '&ajax=&dialog=',
									highlight	: false,
									data		: '',
									type		: "POST",
									success		: function(response, base) {
										if (response !== undefined && response !== null) {
									
											// Check the response; the called form may have reported an error
											// or other response that requires a redirect
											if (response.status!== undefined && response.status==true) {
										
												if(response.redirect!=undefined && response.redirect!='') {
													window.location.href=response.redirect;
												}
											}
											else
											{
											// Output the response to the dialog box
												base.processResponse(response);
												
												var buttons	= {};
												
												$.extend(buttons, closeButton);
												$.extend(buttons, saveButton);

												$('#add_event').dialog("option", "title", 'Edit Holiday Request');
												$('#add_event').dialog("option", "buttons", buttons);
												$('#add_event').dialog('open');
											}
										}
									}
								});
			                  break;

			                }


			            },
			            items: items
			        });
		    	}
		    },
		    
			select: function(start, end, allDay) {
				
				var start_day		= formatDay(start.getDate()),
					start_month		= formatMonth(start.getMonth()),
					start_hour		= formatHour(start.getHours()),
					start_minute	= formatMinute(start.getMinutes()),
					start_date		= start_day+'/'+start_month+'/'+start.getFullYear(),
					end_day			= formatDay(end.getDate()),
					end_month		= formatMonth(end.getMonth()),
					end_hour		= formatHour(end.getHours()),
					end_minute		= formatMinute(end.getMinutes()),
					end_date		= start_day+'/'+start_month+'/'+start.getFullYear();
				
				$('#add_event').dialog('open');
			
				$('#add_event').uz_ajax({
					url			: '/?module=hr&controller=holidayrequests&action=_new&start_date=' + start_date + '&end_date=' + end_date + '&ajax=&dialog=',
					highlight	: false,
					data		: '',
					type		: "POST",
					success		: function(response, base) {
						if (response !== undefined && response !== null) {
							
						// Check the response; the called form may have reported an error
						// or other response that requires a redirect
							if (response.status!== undefined && response.status==true) {
						
								if(response.redirect!=undefined && response.redirect!='') {
									window.location.href=response.redirect;
								}
							}
							else
							{
							// Output the response to the dialog box
								base.processResponse(response);
								$('#add_event').dialog("option", "title", 'Add Holiday Request');
								$('#add_event').dialog('open');
								
								if (start.getFullYear()+'-'+start_month+'-'+start_day < $("#Holidayrequest_today").val()) {
									alert("Warning:\n\nStart Date is before today");
								}
								
							}
						}
					}
				});
			
			},
			eventClick: function(calEvent, jsEvent, view) {
				if (calEvent.id && calEvent.editable) {
				
					$('#add_event').uz_ajax({
						url			: '/?module=hr&controller=holidayrequests&action=edit&id=' + calEvent.id + '&ajax=&dialog=',
						highlight	: false,
						data		: '',
						type		: "POST",
						success		: function(response, base) {
							if (response !== undefined && response !== null) {
						
								// Check the response; the called form may have reported an error
								// or other response that requires a redirect
								if (response.status!== undefined && response.status==true) {
							
									if(response.redirect!=undefined && response.redirect!='') {
										window.location.href=response.redirect;
									}
								}
								else
								{
								// Output the response to the dialog box
									base.processResponse(response);
									
									var buttons	= {};
									
									$.extend(buttons, closeButton);
									$.extend(buttons, saveButton);

									$('#add_event').dialog("option", "title", 'Edit Holiday Request');
									$('#add_event').dialog("option", "buttons", buttons);
									$('#add_event').dialog('open');
								}
							}
						}
					});
				}
				else if (calEvent.status == 'D') {
					alert("Holiday Request declined\n\n"+calEvent.reason_declined);
				}
				else if ($(this).hasClass('fc_public_holidays')) {
					// External feed - disable click
					return false;
				}
			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			
				$.uz_ajax({
					url:'/',
					data:{
						module		: "hr",
						controller	: "holidayrequests",
						action		: "updateEvent",
						type		: "drop",
						id			: event.id,
						day			: dayDelta,
						minute		: minuteDelta
					},
					success: function(response) {
						if(response.success === false) {
							revertFunc();
							$('#flash').find('ul#errors').remove();
							$('#flash').append("<ul id='errors'><li>Error updating holiday request</li></ul>");
						}
					}
				});
			
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			
				$.uz_ajax({
					url:'/',
					data:{
						module		: "hr",
						controller	: "holidayrequests",
						action		: "updateEvent",
						type		: "resize",
						id			: event.id,
						day			: dayDelta,
						minute		: minuteDelta
					},
					success: function(response) {
						if(response.success === false) {
							revertFunc();
							$('#flash').find('ul#errors').remove();
							$('#flash').append("<ul id='errors'><li>Error updating holiday request</li></ul>");
						}
					}
				});
			},
			loading: function( isLoading, view ) {
				if(isLoading==true) {
					$(".page_title").html("Holidays calendar [loading...]");
				} else {
					$(".page_title").html("Holidays Calendar");
				}
			       
			},
			windowResize: function(view) {
				$('.fc-agenda-body',this).css('height','auto');
			}
		});

		$('#sidebar').append($('#legend').show());
	
		$('#legend ul li','#sidebar').hover(
				function () {
					if(!$(this).hasClass('opacity-50')) {
						$('.fc-event','#calendar').addClass('opacity-50');
						var calendar_class = $(this).attr('class');
						$('.fc-event','#calendar').each(function() {
							if($(this).hasClass(calendar_class)) {
								$(this).removeClass('opacity-50');
							}
						});
					}
				}, 
				function () {
					$('.fc-event','#calendar').removeClass('opacity-50');
				}
		);
	
		$('#add_event').dialog({
			title: '',
			modal: true,
			resizable: true,
			autoResize: true,
			autoOpen: false,
			width: 600,
			height: 600,
			overlay: {
				opacity: 0.5,
				background: "black"
			},
			open		: function() {

				var buttons	= {};
				
				$.extend(buttons, saveButton);
				$.extend(buttons, closeButton);
			
				$(this).dialog("option", "buttons", buttons);
			
			},
			close: function(event, ui) { 
				if($calendar.data('event_status') === true) {
					// we're refetching the events from the data source
					// if we could pass data between the dialog and this function we'd be able to paint it directly
					// but it's better than forcing a refresh
					$calendar.fullCalendar('refetchEvents');
				} else {
					$("input[type=text],select","#add_event").each(function() {
						$(this).removeAttr('style').removeAttr('disabled').val('');
					});
					$("input[type=submit]","#add_event").removeAttr('disabled');
				
				}
				$calendar.fullCalendar('unselect');
			}

		});

		$('form', '#add_event').submit(function (event) {
		
			event.preventDefault();
			
			var $self		= $(this),
				error_count = 0;
		
			$("input[type=submit]", $self).attr('disabled','disabled');
		
			$(".required", $self).each(function() {
				if($(this).val()=='') {
					error_count++;
					$(this).css('borderColor','red');
				}
			});
		
			if (error_count > 0) {
				$("input[type=submit]", "#add_event").removeAttr('disabled');
				return false;
			}
		
			$.uz_ajax({
				type	: "POST",
				data	: $self.serialize(),
				url		: $self.attr('action')+'&ajax=',
				success	: function(response) {
			
					$calendar.data('event_status', false);
				
					if(response.success === true) {
					
						$("input[type=text], select", "#add_event").each(function() {
							$(this).removeAttr('style').removeAttr('disabled').val('');
						});
					
						$("input[type=submit]", "#add_event").removeAttr('disabled');

						$calendar.data('event_status', true);
					
						$('#add_event').dialog("close");
					
					}
				
				}
		
			});
		
		});
	
		$('.fc-agenda-body').css('height','auto');

		if (document.getElementById('calendar'))	{
			$('#save_form').addClass('ignore_rules');
		}
	
		var public_holidays =  $.fullCalendar.gcalFeed(
//			'http://www.google.com/calendar/feeds/en.uk%23holiday%40group.v.calendar.google.com/public/basic',
			'https://www.google.com/calendar/feeds/nhlu96v728ekb5cp8tprfhrnbg%40group.calendar.google.com/public/basic',
			{
				// put your options here
				className:'fc_public_holidays',
				editable:false,
				currentTimezone:'Europe/London'
			}
		);
		$calendar.fullCalendar('addEventSource', public_holidays);
	
	}

	hr_calendar();
	
	$('#search_submit, #search_clear', "#hr-holidayrequests-index").live('click', function (event) {

		event.preventDefault();
		
		var form = $(event.currentTarget).parents('form');
		
		// if we want to set other rules for the form, include the ignore_rules class

		// jQuery will not serialize submit buttons, so append the button name and value to the data
		var form_data = form.serialize() + "&" + $(this).attr("name") + "=" + $(this).attr("value") + "&ajax=''";
		
		$('#included_file').uz_ajax({
			type		: 'POST',
			async		: false,
			url			: form.attr('action') + "&ajax=",
			data		: form_data,
			highlight	: false,
			complete	: function() {
				check_if_table_needs_scroll_bar();	
			}
		});
			
		hr_calendar();
		
	});
	
});