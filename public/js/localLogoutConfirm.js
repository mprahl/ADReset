$(document).ready(function() {
    $('.navbar-brand').on('click', function() {
        bootbox.confirm("Going to the homepage will log you out as a local administrator.<br />Would you like to continue?", function(result) {
            if (result) {
                window.location.href = "/index.php";
            }
        }); 
    });
});