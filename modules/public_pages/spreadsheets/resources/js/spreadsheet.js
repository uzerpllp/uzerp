 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

Doc = {
		lastId: 0
};
Date.format='yyyy-mm-dd';

$(document).ready(function($){

	$.fn.spreadsheetManager = function(){
		
		var tables = this;
		
		return tables.each(function(){
			var $table = $(this);
			
			$table.init = function(){
				this.setupInputListeners($table.find('tbody tr'));
				this.setupActionListeners();
				this.setupSearch();
			};
			
			$table.createRow = function(){
				
				Doc.lastId++;
				var id = 'n'+(Doc.lastId);
				$row = $('<tr>').addClass('data')
							.attr('id','row-'+id)
							.append(
									$('<td>').addClass('control')
											.append(
													$('<input type="checkbox">').attr('name','row['+id+'][select]').attr('value',id)
													)	
									);
				$.each(Doc.fields, function(val,field){
						if(val!='id'){
							$input = $table.createInput(field, id);
							$row.append(
										$('<td>').addClass('column')
											.append($input)
									)
						}
				});
				$row.append($('<td>').addClass('indicator').addClass('warn'));
				$table.setupInputListeners($row);
				return $row;
			}
			
			$table.createInput = function(field, id, value){
				switch(field.type){
				case 'select':
					$input = $('<select>').attr('name','row['+id+']['+field.name+']');
					$.each(field.options, function(i,v){
						$option = $('<option>').attr('value',i).text(v);
						if(value!=null && value==i){
							$option.attr('selected','selected');
						}
						$input.append($option);
					});
					break;
				case 'date':
					$input = $('<input>').attr('name','row['+id+']['+field.name+']').addClass(field.type);
					if(value!=null){
						$input.val(value);
					}
					break;
				case 'int':
				case 'float':
					$input = $('<input>').attr('name','row['+id+']['+field.name+']').addClass(field.type);
					if(value!=null){
						$input.val(value);
					}
				default:
					$input = $('<input>').attr('name','row['+id+']['+field.name+']').addClass(field.type).attr('maxlength',field.max_length);
					if(value!=null){
						$input.val(value);
					}
				}
				return $input;
			}
			
			$table.createRowWithData = function(data, warn){
				var id = data.id;
				$row = $('<tr>').addClass('data')
							.attr('id','row-'+id)
							.append(
									$('<td>').addClass('control')
											.append(
													$('<input type="checkbox">').attr('name','row['+id+'][select]').attr('value',id)
													)	
									);
				$.each(Doc.fields, function(val,field){
						if(val!='id'){
							$input = $table.createInput(field, id,data[field.name]);
							$row.append(
										$('<td>').addClass('column')
											.append($input)
									)
						}
				});
				if(warn==null || warn==true){
					$row.append($('<td>').addClass('indicator').addClass('warn'));
				} else {
					$row.append($('<td>').addClass('indicator'));
				}
				$table.setupInputListeners($row);
				return $row;
			}
			
			$table.duplicateRow = function(source){
				Doc.lastId++;
				var id = 'n'+(Doc.lastId);
				$row = $('<tr>').addClass('data')
							.attr('id','row-'+id)
							.append(
									$('<td>').addClass('control')
											.append(
													$('<input type="checkbox">').attr('name','row['+id+'][select]').attr('value',id)
													)	
									);
				$.each(Doc.fields, function(val,field){
						if(val!='id'){
									
							$input = $table.createInput(field, id,$("input[name='row["+source+"]["+field.name+"]'], select[name='row["+source+"]["+field.name+"]'] option:selected").val());
							$row.append(
										$('<td>').addClass('column')
											.append($input)
										);
											
									
						}
				});
				$row.append($('<td>').addClass('indicator').addClass('warn'));
				$table.setupInputListeners($row);
				return $row;
			}
			
			
			$table.setupActionListeners = function(){
				// I know this is lazy but I'm assuming 1 spreadsheet per table
				$('.duplicate_action').click(function(e){
					
				});
				$('.save_action').click(function(e){$table.saveAction();});
				$('.delete_action').click(function(e){$table.deleteAction();});
				$('.add_action').click(function(e){$table.addAction();});
				$('.duplicate_action').click(function(e){$table.duplicateAction();});
				$('.search_action').click(function(e){$('thead tr.search').show();});
				$('thead th.col').click(function(e){$table.orderAction(e);});
				$('thead tr.search td.control').click(
						function(e){
							$('thead tr.search').hide();
							$table.find('thead tr.search input').val('');
							$table.find('thead td option:selected').removeAttr('selected');
							$table.find('thead td option.empty').attr('selected','selected');
							
						});
				
				
				
			};
			
			
			$table.setupSearch = function(e){
				$table.find('thead tr.search').hide();
				$table.find('thead tr.search input, thead tr.search select').change(function(e){
					var url="/?pid="+Doc.pid+"&module=spreadsheets&controlle=index&action=save";
					if(Doc.order !=null){
						url+="&order="+Doc.order;
					}
					$table.find('thead tr.search').addClass('wait');
					$.post(url, $table.parents('form').serialize(),
							  function(data){
									if(data.status=='OK'){
										Doc.rows = data.rows;
										$table.reloadData();
										
									} else {
										$('#flash').empty().append(
												$('<ul>').addClass('errors').attr('id','errors').append(
															$('<li>').html('Failed to search, please try again.')
														)
											);	
										setTimeout(function(){
											new Effect.Fade('messages');
										},2000);
									}
									$table.find('thead tr.search').removeClass('wait');
							  }, "json");	
				});
			}
			
			$table.orderAction = function(e){
				var $th = $(e.target);
				if(Doc.order==$th.text()){
					Doc.order = '_'+$th.text();
				} else {
					Doc.order = $th.text();
				}
				
				
				if($table.find('.changed').length && confirm("Changing order will save the document, do you want to continue?")){
					$table.saveAction();
				} else {
					var url="/?pid="+Doc.pid+"&module=spreadsheets&controlle=index&action=reload&order="+Doc.order;
					$.post(url, $table.parents('form').serialize(),
							  function(data){
									if(data.status=='OK'){
										Doc.rows = data.rows;
										$table.reloadData();
									} else {
										$('#flash').empty().append(
												$('<ul>').addClass('errors').attr('id','errors').append(
															$('<li>').html('Failed to save, please try again.')
														)
											);	
										setTimeout(function(){
											new Effect.Fade('messages');
										},2000);
									}
							  }, "json");
				}
			}
			
			$table.duplicateAction = function(){
				$tbody = $table.find('tbody');
				$table.find('input:checked').each(function(){
					$tbody.append($table.duplicateRow($(this).val()));
					$(this).parents('tr').each(function(){
						$(this).removeClass('selected');
					});
					$(this).removeAttr('checked');
				});
			}

			/**
			 * Save Document Data
			 */
			$table.saveAction = function(){
				
				var url="/?pid="+Doc.pid+"&module=spreadsheets&controlle=index&action=save";
				if(Doc.order !=null){
					url+="&order="+Doc.order;
				}
				$.post(url, $table.parents('form').serialize(),
						  function(data){
								if(data.status=='OK'){
									Doc.rows = data.rows;
									$table.reloadData();
									$('#flash').empty().append(
											$('<ul>').addClass('messages').attr('id','messages').append(
														$('<li>').html('Saved Successfully')
													)
										);	
									setTimeout(function(){
										new Effect.Fade('messages');
									},2000);
								} else {
									$('#flash').empty().append(
											$('<ul>').addClass('errors').attr('id','errors').append(
														$('<li>').html('Failed to save, please try again.')
													)
										);	
									setTimeout(function(){
										new Effect.Fade('messages');
									},2000);
								}
						  }, "json");
				
			};
			
			$table.reloadData = function(){
				var $tbody = $table.find('tbody');
				$tbody.empty();
				$.each(Doc.rows, function(i,row){
					$tbody.append($table.createRowWithData(row,false));
				});
			}
			
			/**
			 * Add Table Row
			 */
			$table.addAction = function(){
				$row = $table.createRow();
				$table.append($row);
				$row.find('input[type=text]:first').focus();
				
			};
			
			/**
			 * Delete Selected Rows
			 */
			$table.deleteAction = function(){
				
				if(!$table.find('input:checked').length){
					return;
				}

				
				var ids = [];
				
				$table.find('input:checked').each(function(){
					//Check for OTF records
					if($(this).val().charAt(0)=='n'){
						//Safe to remove as these were created on-the-fly
						$(this).parents('tr').remove();
					} else {
						ids.push($(this).val());
					}
				});
				
				if(ids.length){
					if(!confirm('Are you sure you wan\'t to permanently delete these rows?')){
						return;
					}
					//Do ajax delete
					var url="/?pid="+Doc.pid+"&module=spreadsheets&controlle=index&action=delete";
					
					$.post(url, $table.parents('form').serialize(),
							  function(data){
								if(data.status =='OK'){
								   	$.each(ids,function(key,value){
								   		$('#row-'+value).remove();
								   	});
									$('#flash').empty().append(
											$('<ul>').addClass('messages').attr('id','messages').append(
														$('<li>').html('Row(s) deleted')
													)
										);	
									setTimeout(function(){
										new Effect.Fade('messages');
									},2000);
								} else {
									$('#flash').empty().append(
											$('<ul>').addClass('errors').attr('id','errors').append(
														$('<li>').html('Failed to delete rows, please try again.')
													)
										);	
									setTimeout(function(){
										new Effect.Fade('messages');
									},2000);
								}
							  }, "json");
				}
					
			};
			
			$table.validateInput = function($input){
				if($input.hasClass('int')){
				
					value=$input.val();
					if(value.length){
						for (i = 0; i < value.length; i++)
						{
							var c = value.charAt(i);
							if((c <"0") || (c >"9")){
								$input.parents('td').addClass('error');
								return false;
							}
						}	
					}
				}
				
				$input.parents('td').removeClass('error');
				return true;
			}
			
			/**
			 * Add Listeners to Input 
			 */
			$table.setupInputListeners = function($row){
			
				$row.find('td.column input, td.column select').focus(function(e) {
					$(e.target).addClass('has_focus');
					Doc.lastval = $(e.target).val();
					$(e.target).parents('td').each(function(){
						$(this).addClass('active');
					});
					
					$(e.target).parents('tbody').find('tr').each(function(){
						$(this).removeClass('selected');
						$(this).find('td.control input').each(function(){
								$(this).removeAttr('checked');
							});
					});
					$(e.target).select();
				}).keydown(function(e){
					$input = $(e.target);
					$parentRow = $input.parents('tr');
					switch(e.keyCode){
					case 38://up
							if($parentRow.is(':first-child')){
								return;
							}
							
							var rowname = $input.attr('name');
							var matches = rowname.match(/row\[[n|0-9]*?\]\[(.*)\]/);
							if(matches.length==2){
								$prev = $parentRow.prev('tr');
								if($prev.length){
									var id = $prev.find('td.control input').val();
									$input.trigger('blur');
									$prev.find("input[name='row["+id+"]["+matches[1]+"]']").trigger('focus');
								}
							}
						break;
					case 40://down
						if($parentRow.is(':last-child')){
							return;
						}
						var rowname = $input.attr('name');
						var matches = rowname.match(/row\[[n|0-9]*?\]\[(.*)\]/);
						if(matches.length==2){
							$next = $parentRow.next('tr');
							if($next.length){
								var id = $next.find('td.control input').val();
								$input.trigger('blur');
								$next.find("input[name='row["+id+"]["+matches[1]+"]']").trigger('focus');
							}
						}
						break;
					}
				}).blur(function(e){
					$input = $(e.target);
					if(Doc.lastval != $input.val()){
						$input.addClass('changed');
						$input.parents('tr').find('td.indicator').addClass('warn');
					}
					if(!$table.validateInput($input)){
						//e.stopPropagation();
						$input.focus();
						return;
					}
					$(e.target).removeClass('has_focus');
					$(e.target).parents('td').each(function(){
						$(this).removeClass('active');
						if($(this).is(':last-child')){
							$(this).parents('tr').each(function(){
								if($(this).is(':last-child')){
									$row = $table.createRow();
									$(this).parent().append($row);
									$row.find('input[type=text]:first').focus();					
								}
							});
						}
					});
				});
				$row.find('select').change(function(e){
					$(e.target).parents('tr').find('td.indicator').addClass('warn');
				});
				$row.find('td.control input').change(function(){
					if($(this).is(':checked')){
						$(this).parents('tr').each(function(){$(this).addClass('selected')});
					} else {
						$(this).parents('tr').each(function(){$(this).removeClass('selected')});
					}
				});		
				$row.find('input.int').keydown(function(e){
					if(!((e.keyCode >= 96 && e.keyCode <= 105) || (e.keyCode >= 48 && e.keyCode <= 57))   && e.keyCode !=8 && e.keyCode !=9 && e.keyCode !=37 && e.keyCode !=39 ){
						e.preventDefault();
						return false;
					}
				});
				$row.find('input.float').keydown(function(e){
					if(!((e.keyCode >= 96 && e.keyCode <= 105) || (e.keyCode >= 48 && e.keyCode <= 57))   && e.keyCode !=9 && e.keyCode !=8 && e.keyCode !=37 && e.keyCode !=39 && e.keyCode !=110 && e.keyCode !=190 ){
						e.preventDefault();
						return false;
					}
				});
				$row.find('input.date').datepicker();
			};
			$table.init();
		});
		
	};
	$('table#spreadsheet').spreadsheetManager();
	
	
	
	
});

