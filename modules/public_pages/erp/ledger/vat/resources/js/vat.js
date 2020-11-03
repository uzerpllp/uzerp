 
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

	// Fetch various information for MTD VAT fraud headers
	var fp = {
		ip: '',
		pixelRatio: '',
		screenWidth: '',
		screenHeight: '',
		colorDepth: '',
		windowWidth: '',
		windowHeight: '',
		plugins: '',
		userAgent: '',
		dnt: ''
	}

	const ip = new Promise((resolve, reject) => {
		const conn = new RTCPeerConnection()
		conn.createDataChannel('')
		conn.createOffer(offer => conn.setLocalDescription(offer), reject)
		conn.onicecandidate = ice => {
		  if (ice && ice.candidate && ice.candidate.candidate) {
			resolve(ice.candidate.candidate.split(' ')[4])
			conn.close()
		  }
		}
	  })
	
	ip.then(result => {
		fp.ip = result
	})
		
	fp.pixelRatio = window.devicePixelRatio;
	fp.screenWidth = window.screen.width;
	fp.screenHeight = window.screen.height;
	fp.colorDepth = window.screen.colorDepth;
	fp.windowWidth = window.outerWidth;
	fp.windowHeight = window.outerHeight;
	
	fp.plugins = navigator.plugins;

	fp.userAgent = navigator.userAgent;

	var dnt = "false";
	if (navigator.doNotTrack == 1) {
		dnt = "true";
	}
	fp.dnt = dnt;
	
	
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
									fp      :JSON.stringify(fp),
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
