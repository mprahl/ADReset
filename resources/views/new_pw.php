<?php
  require_once(RESOURCE_DIR . 'functions/ADPasswordPolicyMatch.php');
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Set New Password';
	require_once(RESOURCE_DIR . 'templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
	require_once(RESOURCE_DIR . 'templates/not_loggedin_navigation.php');
?>
<!-- Navigation Menu Ends -->
<!-- Content Starts -->
<div class="container" id="mainContentBody">

<!-- Start of form inspired from http://bootswatch.com/flatly/ -->
<div class="col-md-12">
    <form class="form-horizontal" method="post" action="newpw.php" name="newpwform">
      <fieldset>
        <h2 class="topHeader">Set A New Password</h2>
		<div class="col-md-12">
		    <?php
		        // Show potential feedback from the login object
		        if (flashMessage::flashIsSet('NewPWError')) {
		            FlashMessage::displayFlash('NewPWError', 'error');
		        }
		        elseif (flashMessage::flashIsSet('NewPWMessage')) {
		            FlashMessage::displayFlash('NewPWMessage', 'message');
		        }
		    ?>
		</div>
		<div class="col-md-12">
            <div class="well resetPwWell">
			To reset your password, simply type in your new password in each text box and click on the &quot;Reset Password&quot; button. Please remember that your new password must conform to the company's <a href="#" class="tool-tip" data-html="true" data-toggle="tooltip" data-placement="top" data-original-title="<?php  echo ADPasswordPolicyWritten() ?>">password policy</a>.
			</div>
        </div>
        <div class="form-group">
            <label for="login_input_password_new" class="col-lg-2 control-label">Password:</label>
            <div class="col-lg-10">
                <input id="login_input_password_new" class="form-control" type="password" name="user_password_new" placeholder="Password" required autocomplete="off" />
            </div>
        </div>

        <div class="form-group">
            <label for="login_input_password_repeat" class="col-lg-2 control-label">Repeat Password:</label>
            <div class="col-lg-10">
                <input id="login_input_password_repeat" class="form-control" type="password" name="user_password_repeat" placeholder="Repeat Password" required autocomplete="off" />
            </div>
        </div>
        <input type="hidden" name="id" value="<?php if (isset($_GET['id'])) { echo $_GET['id']; } elseif(isset($_GET['idq'])) { echo $_GET['idq']; } ?>">
        <br />
        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary" name="setPassword" value="Set Password">Set Password</button>
            <button class="btn btn-default" type="reset">Reset Form</button>
          </div>
        </div>
      </fieldset>
    </form>
</div>
<!-- End of Form -->

</div>
<!-- Content Ends -->
</body>
</html>