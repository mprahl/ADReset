$(document).ready(function(){
    $('#resetpw').on('click', function(e) {
        bootbox.dialog({
          message: "By which method would you like to reset your password?",
          title: "Reset Password",
          buttons: {
            success: {
              label: "Email",
              className: "btn-primary",
              callback: function() {
                window.location.href = "/resetpwemail.php";
              }
            },
            main: {
              label: "Secret Questions",
              className: "btn-default",
              callback: function() {
                window.location.href = "/resetpw.php";
              }
            }
          }
        });

        return false;
    });
});
