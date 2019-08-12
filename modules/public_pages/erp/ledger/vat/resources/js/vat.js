 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * vat.js
 * 
 * 	$Revision: 1.7 $
 *
 */

$(document).ready(function(){
	
	// custom confirmation message
	$(document).on('click', 'a.vat-confirm', {}, function(event){
		event.preventDefault();
		var message = 'Are you sure?';
		if ($( this ).data('uz-confirm-message') !== undefined) {
			message = $( this ).data('uz-confirm-message').split('|');
		}
		
		var targetUrl = $(this).attr('href');
		var actionID = $(this).data('uz-action-id');
		
		$( '<div id="#confirm-dialog" title="Confirm Action"><p><strong>' + message[0] + '</strong></p>\
		<p><em>' + (message[1] !== undefined ? message[1] : '') + '</em></p></div>'
			).dialog({
				resizable: false,
				modal: true,
				buttons: {
					"Yes": function() {
						if ( typeof actionID != 'undefined' || actionID != null) {
							$.uz_ajax({
								async       : true,
								type        : 'POST',
								url         : targetUrl,
								data: {
									id      : actionID,
									dialog  : true,
									ajax    : true
								},
								block : function() {
									$.blockUI();
								}, 
								success: function(data) {
									if (typeof data.redirect != 'undefined' || data.redirect != null) {
										window.location.href = data.redirect;
									} else {
										//uzERP returned empty or unexpected response
										$('#flash').append("<ul id='errors'><li>Action failed</li></ul>");
									}
								},
							});
							$( this ).dialog( "close" );
						} else {
							$( this ).dialog( "close" );
							alert("Invalid Data");
						}
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
			});
	});

	/* vat -> vat -> enter_journal */
	
	$("#Vat_glaccount_id", "#vat-vat-enter_journal").live("change", function(){
		
		var $self = $(this);
		
		$('#Vat_glcentre_id').uz_ajax({
			data:{
				module		: 'vat',
				controller	: 'vat',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});	
		
});
