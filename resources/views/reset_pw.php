<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Reset Password';
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
    <form class="form-horizontal" method="post" action="resetpw.php" name="resetpwform">
      <fieldset>
        <h2 class="topHeader">Reset Password</h2>
		<div class="col-md-12">
		    <?php
		        // Show potential feedback from the login object
		        if (flashMessage::flashIsSet('ResetPWError')) {
		            FlashMessage::displayFlash('ResetPWError', 'error');
		        }
		        elseif (flashMessage::flashIsSet('ResetPWMessage')) {
		            FlashMessage::displayFlash('ResetPWMessage', 'message');
		        }
		    ?>
		</div>
		<div class="col-md-12">
            <div class="well resetPwWell">
			         This form will allow you to reset your password using the secret questions you defined earlier. If you have not done so, you will not be able to use this feature, therefore, contact the Help Desk for assistance with your password reset. 
			     </div>
    </div>
    <div class="form-group">
    	<label for="inputUsername" class="col-lg-2 control-label"><a href="#" class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Enter the username you use to login to Windows.">Username:</a></label>
    	<div class="col-lg-10">
    		<input type="text" class="form-control" id="inputUsername" placeholder="Username" name="user_name">
      	</div>
    </div>
    <br />
    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="submit" class="btn btn-primary" name="resetPasswordWithQuestions" value="Reset">Reset Password</button>
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