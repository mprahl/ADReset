$(document).ready(function() {
	bootbox.alert("You have not set configured Secret Questions yet. In order to be able to reset your Windows (Active Directory) password using Secret Questions, you will need to login and set them.", function() {
		window.location.href = "/account.php";
	});
});