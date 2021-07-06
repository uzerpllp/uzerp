 
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
		iptime: new Date().toISOString(),
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

	var getBrowserPlugins = function(navigator) {
		var rdls = function (vals) {
			var res = [];
			var tmp = vals.sort();
	
			for (var i = 0; i < tmp.length; i++) {
				res.push(tmp[i]);
				while (JSON.stringify(tmp[i]) == JSON.stringify(tmp[i + 1])) {
					i++;
				}
			}
	
			return res;
		};
	
		var res = [];
		if (!navigator || !navigator.plugins) {
			return res;
		}
					
		for (var p in navigator.plugins) {
			var plugin = navigator.plugins[p];
	
			for (var j = 0; j < plugin.length; j++) {
				var mime = plugin[j];
	
				if (!mime) {
					continue;
				}
	
				var ep = mime.enabledPlugin;
				if (ep) {
					var item = {
						mime: mime.type,
						name: ep.name,
						description: ep.description,
						filename: ep.filename
					};
	
					res.push(item);
				}
			}
		}
	
		return rdls(res);
	};

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
	
	fp.plugins = getBrowserPlugins(navigator);

	fp.userAgent = navigator.userAgent;

	var dnt = "false";
	if (navigator.doNotTrack == 1) {
		dnt = "true";
	}
	fp.dnt = dnt;
	
	console.log(fp);
	
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


	/* vat -> vat -> import_pva */

	$("#vat-vat-enterpvaentry").on("change", "#Vat_glperiods_id",  function(){

		var $self = $(this);
		
		$('#Vat_invoice').uz_ajax({
			data:{
				module		: 'vat',
				controller	: 'vat',
				action		: 'getPVAInvoices',
				period_id	: $self.val(),
				ajax		: ''
			}
		});
		
	});

	$("#vat-vat-enterpvaentry").on("change", "#Vat_invoice",  function(){

		var $self = $(this);
		
		$('#Vat_transaction_date').uz_ajax({
			data:{
				module			: 'vat',
				controller		: 'vat',
				action			: 'getPVAEntryDateDefault',
				invoice_number	: $self.val(),
				ajax			: ''
			}
		});
		
	});
		
});
