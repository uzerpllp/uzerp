 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * contacts.js
 * 
 * $Revision: 1.14 $
 * 
 */

$(document).ready(function() {
	
	/* contacts -> persons -> new */
	
	$("#Address_fulladdress", "#contacts-persons-new").live('change',function(){
		if ($('#Address_fulladdress').val() == '')
		{
			$("#address").show();
			$("#Address_id").val('');
			$("#PartyAddress_address_id").val('');
		}
		else
		{
			$("#address").hide();
			$("#Address_id").val($('#Address_fulladdress').val());
			$("#PartyAddress_address_id").val($('#Address_fulladdress').val());
		}
	});
	
	$("#Person_company_id", "#contacts-persons-new").live('change', function(){
		
		var $self = $(this);
		
		$('#Address_fulladdress').uz_ajax({
			data:{
				module		: 'contacts',
				controller	: 'persons',
				action		: 'getAddresses',
				company_id	: $self.val(),
				person_id	: $('#Person_id').val(),
				fulladdress	: $('#Address_fulladdress').val(),
				ajax		: ''
			}
		});
		
	});
	
});
