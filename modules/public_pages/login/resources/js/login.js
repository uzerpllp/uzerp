 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

$(document).ready(function() {

	new ClipboardJS('.btn');

	/**
	 * Handle the cancel button on mfavalidate
	 */
	const button = document.querySelector('button#cancel');
	if (button) {
		button.addEventListener('click', (event) => {
			event.preventDefault();
			const formElement = document.querySelector("form");
			const formData = new FormData(formElement);
			formData.append("cancel", true);

			fetch('/?action=mfavalidate', {
				method: 'post',
				body: formData
			}).then(function(response) {
				return response.text();
			}).then(function(text) { 
				document.open();
				document.write(text);
				document.close();
			});
		});
	};

});
