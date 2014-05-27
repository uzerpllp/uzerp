/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * general_ledger.js
 * 
 * 	$Revision: 1.17 $
 * 
 */

 function calcJournalTotal (parent, target) {
	 
	var total = 0;
	
	$(parent).find('.debit').each(function(id) {
		total += isNaN(parseFloat($(this).val()))?0:parseFloat($(this).val());
		total  = parseFloat(total.toFixed(2));
	});
	
	$(parent).find('.credit').each(function(id) {
		total -= isNaN(parseFloat($(this).val()))?0:parseFloat($(this).val());
		total  = parseFloat(total.toFixed(2));
	});
	
	$(target).val(total.toFixed(2));
	
}
 
$(document).ready(function(){
	
	/* #general_ledger -> gltransactionheaders -> new */

	$("#GLTransactionHeader_transaction_date","#general_ledger-gltransactionheaders-new").live("change", function() {
		
		var $self = $(this);
		
		$('#GLTransactionHeader_period').uz_ajax({
			data: {
				module		: 'general_ledger',
				controller	: 'gltransactionheaders',
				action		: 'getPeriod',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
		$('#GLTransactionHeader_accrual_period_id').uz_ajax({
			data: {
				module		: 'general_ledger',
				controller	: 'gltransactionheaders',
				action		: 'getPeriods',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#GLTransactionHeader_accrual", "#general_ledger-gltransactionheaders-new").live("click", function() {
	
		if ($(this).prop('checked')) {
			$('#GLTransactionHeader_accrual_period_id').removeAttr('disabled');
		} else {
			$('#GLTransactionHeader_accrual_period_id').attr('disabled', 'disabled');
		}
	
	});
	
	$("#GLTransactionHeader_type", "#general_ledger-gltransactionheaders-new").live("change", function() {
//		alert('change type is '+$(this).val());
		if ($(this).val()=='T') {
			$('.standard_field').hide();
		} else {
			$('.standard_field').show();
		}
	
	});
	
	$("#GLTransactionHeader_type").change();
	
	/* general_ledger -> gltransactionheaders -> view */
	
	$(".unposted .row_docref a, .add_journal_line_related a").live('click', function(event){
		
		event.preventDefault();
		
		var $self = $(this);
		
		if ($self.parent('li').hasClass('add_lines_related')) {
			var title='Add GL Journal Detail';
			var type='add';
		} else {
			var title='Edit GL Journal Detail';
			var type='edit';
		}

		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'gltransactions',
			url			: $self.attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true,
			callback	: function() {
				$("#GLUnpostedTransaction_reference").focus();
			}
		});
		
	});
	
	/* #general_ledger -> gltransactions -> new */

	$("#GLUnpostedTransaction_glaccount_id","#general_ledger-gltransactions-new").live("change", function() {
		
		var $self = $(this);
		
		$('#GLUnpostedTransaction_glcentre_id').uz_ajax({
			data:{
				module		: 'general_ledger',
				controller	: 'gltransactions',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});	
		
	});
	
	/* general_ledger -> glbudgets -> inputdatagrid */
	
	$('.uz-data-input-spreadsheet td','#general_ledger-glbudgets-inputdatagrid').live('click',function() {
		
		var $this = $(this);
		
		if ($this.children('input').length==0 && $this.index() != 0) {
			
			var current_value = $this.html();

			// clear the current value
			$this.html('');
			
			// create hidden text input
			$this.append('<input type="text" class="uz-data-input-element" style="display: none;" />');
			
			var $input = $this.children('input');
			
			$input.val(current_value)
				  .data('original_value',current_value)
				  .width('100%')
				  .height('100%')
				  .show()
				  .focus()
				  .select();
			
			$this.css('border','3px solid black');
			
		}
		
	});
	
	// define the global var just before the input blur event :-)
	element_count = 0;
	
	$('.uz-data-input-spreadsheet input','#general_ledger-glbudgets-inputdatagrid').live('blur',function() {
		
		var $this = $(this),
			$cell = $this.parent('td');
		
		if ($this.data('original_value')!=$this.val()) {
			
			// the data is about to change
			data_changed = true;
			
			if ($this.val() == '') {
				$this.val('0');
			}
			
			// set a few elements as vars
			var $table		= $this.parents('table'),
				$form		= $table.siblings('form'),
				row_parent	= $cell.parents('tr').attr('rel'),
				cell_index	= $this.parents('td').index(),
				col_parent	= $table.find('tr:first th:eq(' + cell_index + ')').attr('rel');


			// the value has been changed by the user
			var id = null;
			if ($cell.attr('rel') != '') {
				id = $cell.attr('rel');
			}
			
			// remove and element that already exists
			
			element_count++;
			
			// when saving the data we should only pass the id if we have it, otherwise pass the required data
			// when this goes generic, how are we going to know what data needs to be set?
			// could be any number of fields
			
			if (id == null) {
				$form.append('<input type="hidden" name="' + di_class_name + '[' + element_count + '][glperiods_id]" value="' + col_parent + '" />');
				$form.append('<input type="hidden" name="' + di_class_name + '[' + element_count + '][glaccount_id]" value="' + row_parent + '" />');
				$form.append('<input type="hidden" name="' + di_class_name + '[' + element_count + '][glcentre_id]" value="' + search_centre + '" />');
			} else {
				$form.append('<input type="hidden" name="' + di_class_name + '[' + element_count + '][id]" value="' + id + '" />');
			}
			$form.append('<input type="hidden" name="' + di_class_name + '[' + element_count + '][value]" value="' + $this.val() + '" />');
		}
		
		$this.hide();
		
		$cell.html($this.val())
			 .removeAttr('style'); 
		
		// because of the above line we cannot rely on using .style for persistent styling
		
		$this.remove();
		
		
	});
	
	$('.uz-data-input-spreadsheet input','#general_ledger-glbudgets-inputdatagrid').live('keydown',function(event) {
		
		var $this = $(this);
		
		switch(event.keyCode) {
		
			case 9:
				event.preventDefault();
				// bug here if you hold town tab
				// do we want to go next or previous?
				if(event.shiftKey) {
					// shift has been pressed, we want to go previous
					var $prev_cell = $this.parents('td').prev();
					if($prev_cell.length>0) {
						$prev_cell.trigger('click');
					}
					
				} else {
					// shift has not been pressed, go next
					var $next_cell = $this.parents('td').next();
					if($next_cell.length>0) {
						$next_cell.trigger('click');
					}
				}
				break;
				
			case 13:
				event.preventDefault();
				var cell_index = $this.parents('td').index();
				// do we want to go up or down?
				if(event.shiftKey) {
					// shift has been pressed, we want to go up
					var $cell_above = $this.parents('tr').prev().find('td:eq('+cell_index+')');
					if($cell_above.length>0) {
						$cell_above.trigger('click');
					}
				} else {
					// shift has not been pressed, go downwards
					var $cell_below = $this.parents('tr').next().find('td:eq('+cell_index+')');
					if($cell_below.length>0) {
						$cell_below.trigger('click');
					}
				}
				break;
				
			case 38:
				event.preventDefault();
				var cell_index = $this.parents('td').index();
				var $cell_above = $this.parents('tr').prev().find('td:eq('+cell_index+')');
				if($cell_above.length>0) {
					$cell_above.trigger('click');
				}
				break;
				
			case 40:
				event.preventDefault();
				var cell_index = $this.parents('td').index();
				var $cell_below = $this.parents('tr').next().find('td:eq('+cell_index+')');
				if($cell_below.length>0) {
					$cell_below.trigger('click');
				}
				break;
				
		}
		
	});

});