$(document).ready(function() {
	$('.connectionSettingsLink').on('click', function() {
		bootbox.confirm("Connection Settings must be administered as a local administrator.<br />Please login as a local administrator from the localadmin.php login form by clicking OK<br />Please note, you will be logged out of your current session during the process.", function(result) {
			if (result) {
				window.location.href = "/account.php?logout&page=localadmin.php";
			}
		}); 
	});
});