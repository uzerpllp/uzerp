$(document).ready(function() {

	const button = document.getElementById('reset-mfa');
	if (button) {
		button.addEventListener('click', (event) => {
			event.preventDefault();
			const formElement = document.getElementById('save_form');
			const formData = new FormData(formElement);
			formData.append("mfa_reset", true);

			fetch('/?module=admin&controller=users&action=reset_mfa_enrollment', {
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