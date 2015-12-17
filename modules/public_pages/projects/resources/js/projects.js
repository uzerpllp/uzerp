 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * projects.js
 * 
 * $Revision: 1.5 $
 * 
 */

$(document).ready(function() {
	
	/* projects -> projectbudgets -> new */
	
	$("#ProjectBudget_project_id", "#projects-projectbudgets-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#ProjectBudget_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'projects',
				controller	: 'projectbudgets',
				action		: 'getTaskList',
				project_id	: $('#ProjectBudget_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#ProjectBudget_budget_item_type", "#projects-projectbudgets-new").live('change',function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:{
				element	: "#ProjectBudget_budget_item_id",
				field	: 'budget_item_id'
		    },
			data:{
				module				: 'projects',
				controller			: 'projectbudgets',
				action				: 'getBudgetItemList',
				budget_item_type	: $self.val(),
				ajax				: ''
			}
		});
		
	});
	
	$("#ProjectBudget_budget_item_id", "#projects-projectbudgets-new").live('change',function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
			    {
					element	: "#ProjectBudget_description",
					field	: 'description'
		    	},
		    	{
					element	: "#ProjectBudget_uom_id",
					field	: 'uom_id',
					action	: "selected"
		    	},
		    	{
					element	: "#ProjectBudget_cost_rate",
					field	: 'cost_rate'
		    	},
		    	{
					element	: "#ProjectBudget_setup_cost",
					field	: 'setup_cost'
		    	},
		    	{
					element	: "#ProjectBudget_charge_rate",
					field	: 'charge_rate'
		    	},
		    	{
					element	: "#ProjectBudget_setup_charge",
					field	: 'setup_charge'
			   	}
		    ],
			data:{
				module				: 'projects',
				controller			: 'projectbudgets',
				action				: 'getItemDetail',
				budget_item_id		: $self.val(),
				budget_item_type	: $('#ProjectBudget_budget_item_type').val(),
				ajax				: ''
			}
		});
		
	});
		
	/* projects -> projects-projectcostcharges-new */
	
	$("#ProjectCostCharge_item_type", "#projects-projectcostcharges-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#ProjectCostCharge_account",
				field	: 'account'
		    },
			data:{
				module		: 'projects',
				controller	: 'projectcostcharges',
				action		: 'getAccountList',
				item_type	: $('#ProjectCostCharge_item_type').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#ProjectCostCharge_account", "#projects-projectcostcharges-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#ProjectCostCharge_item_id",
				field	: 'item_id'
		    },
			data:{
				module		: 'projects',
				controller	: 'projectcostcharges',
				action		: 'getOrderList',
				account		: $('#ProjectCostCharge_account').val(),
				item_type	: $('#ProjectCostCharge_item_type').val(),
				ajax		: ''
			}
		});
		
	});
	
	/* projects -> hours -> hours_new */
	
	$("#hour_start_time, #Hour_owner", "#projects-hours-hours_new").live('change',function(){
		
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

	/* projects -> tasks -> new */
	
	$("#Task_project_id", "#projects-tasks-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#Task_parent_id",
				field	: 'parent_id'
		    },
			data:{
				module		: 'projects',
				controller	: 'Tasks',
				action		: 'getTaskList',
				project_id	: $('#Task_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#Task_parent_id", "#projects-tasks-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		        {
		        	element	: "#task_start_date",
		        	field	: "start_date"
		        },
		        {
		        	element	: "#task_start_date_hours",
		        	field	: "start_date_hours"
		        },
		        {
		        	element	: "#task_start_date_minutes",
		        	field	: "start_date_minutes"
		        },
		        {
		        	element	: "#task_end_date",
		        	field	: "end_date"
		        },
		        {
		        	element	: "#task_end_date_hours",
		        	field	: "end_date_hours"
		        },
		        {
		        	element	: "#task_end_date_minutes",
		        	field	: "end_date_minutes"
		        }
		    ],
			data:{
				module		: 'projects',
				controller	: 'Tasks',
				action		: 'getStartEndDate',
				project_id	: $('#Task_project_id').val(),
				task_id		: $('#Task_parent_id').val(),
				ajax		: ''
			}
		});
		
	});

	/* projects -> projectequipmentallocations -> new */
	
	$("#ProjectEquipmentAllocation_project_id", "#projects-projectequipmentallocations-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#ProjectEquipmentAllocation_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'projects',
				controller	: 'projectequipmentallocations',
				action		: 'getTaskList',
				project_id	: $('#ProjectEquipmentAllocation_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#ProjectEquipmentAllocation_project_id, #ProjectEquipmentAllocation_task_id", "#projects-projectequipmentallocations-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		        {
		        	element	: "#ProjectEquipmentAllocation_start_date",
		        	field	: "start_date"
		        },
		        {
		        	element	: "#ProjectEquipmentAllocation_end_date",
		        	field	: "end_date"
		        }
		    ],
			data:{
				module		: 'projects',
				controller	: 'projectequipmentallocations',
				action		: 'getStartEndDate',
				project_id	: $('#ProjectEquipmentAllocation_project_id').val(),
				task_id		: $('#ProjectEquipmentAllocation_task_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#ProjectEquipmentAllocation_project_equipment_id", "#projects-projectequipmentallocations-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		        {
		        	element	: "#ProjectEquipmentAllocation_setup_charge",
		        	field	: "setup_charge"
		        },
		        {
		        	element	: "#ProjectEquipmentAllocation_charge_rate",
		        	field	: "charge_rate"
		        }
		    ],
			data:{
				module					: 'projects',
				controller				: 'projectequipmentallocations',
				action					: 'getEquipmentDetail',
				project_equipment_id	: $('#ProjectEquipmentAllocation_project_equipment_id').val(),
				ajax					: ''
			}
		});
		
	});
	
	$("#ProjectEquipmentAllocation_project_equipment_id, #ProjectEquipmentAllocation_start_date, #ProjectEquipmentAllocation_end_date", "#projects-projectequipmentallocations-new").live('change',function(){
		
		$('#current_allocation').uz_ajax({
			data:{
				module					: 'projects',
				controller				: 'projectequipmentallocations',
				action					: 'getEquipmentAllocation',
				project_equipment_id	: $('#ProjectEquipmentAllocation_project_equipment_id').val(),
				start_date				: $('#ProjectEquipmentAllocation_start_date').val(),
				end_date				: $('#ProjectEquipmentAllocation_end_date').val(),
				ajax					: ''
			}
		});
		
	});

	/* projects -> resources -> new */
	
	$("#Resource_project_id", "#projects-resources-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#Resource_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'projects',
				controller	: 'Resources',
				action		: 'getTaskList',
				project_id	: $('#Resource_project_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#Resource_project_id, #Resource_task_id", "#projects-resources-new").live('change',function(){
		
		$.uz_ajax({
			target:[
		        {
		        	element	: "#Resource_start_date",
		        	field	: "start_date"
		        },
		        {
		        	element	: "#Resource_end_date",
		        	field	: "end_date"
		        }
		    ],
			data:{
				module		: 'projects',
				controller	: 'Resources',
				action		: 'getStartEndDate',
				project_id	: $('#Resource_project_id').val(),
				task_id		: $('#Resource_task_id').val(),
				ajax		: ''
			}
		});
		
	});
	
});
