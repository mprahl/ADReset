<!DOCTYPE html>
<?php
  require_once(RESOURCE_DIR . 'functions/ADPasswordPolicyMatch.php');
  $pageTitle = 'Change Password';
  require_once(RESOURCE_DIR . 'templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
  if (LoginCheck::isLoggedInAsAdmin() && LoginCheck::isDomain()) {
    require_once(RESOURCE_DIR . '/templates/admin_navigation.php');
  }
  elseif (LoginCheck::isLoggedIn() && LoginCheck::isDomain()) {
    require_once(RESOURCE_DIR . '/templates/navigation.php');
  }
  elseif (LoginCheck::isLocal()) {
    header('location: /settings/localusersettings.php');
  }
  else {
      require_once(RESOURCE_DIR . '/templates/not_loggedin_navigation.php');
  }
?>
<!-- Navigation Menu Ends -->
<!-- Content Starts -->
<div class="container" id="mainContentBody">

<!-- Start of form inspired from http://bootswatch.com/flatly/ -->
<div class="col-md-12">
    <form class="form-horizontal" method="post" action="changepw.php" name="loginform">
      <fieldset>
        <h2 class="topHeader">Set Your New Password</h2>
<div class="col-md-12">
    <?php
        // Show potential feedback from the login object
        if (flashMessage::flashIsSet('ChangePWError')) {
            FlashMessage::displayFlash('ChangePWError', 'error');
        }
        elseif (flashMessage::flashIsSet('ChangePWMessage')) {
            FlashMessage::displayFlash('ChangePWMessage', 'message');
        }
    ?>
</div>
<div class="col-md-12">
      <div class="well resetPwWell">
      To set your password, simply fill in the text boxes below and click on the &quot;Change Password&quot; button. Please remember that your new password must conform to the company's <a href="#" class="tool-tip" data-html="true" data-toggle="tooltip" data-placement="top" data-original-title="<?php  echo ADPasswordPolicyWritten() ?>">password policy</a>.
      </div>
</div>
<div class="form-group">
  <label for="inputUsername" class="col-lg-2 control-label"><a href="#" class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="This is the same username that's used to login to your Windows computer.">Username:</a>
  </label>
  <div class="col-lg-10">
    <input type="text" class="form-control" id="inputUsername" placeholder="Enter your username" name="user_name">
  </div>
</div>
<div class="form-group">
  <label for="inputOldPassword" class="col-lg-2 control-label">Current Password:</label>
  <div class="col-lg-10">
    <input type="password" class="form-control" id="inputOldPassword" placeholder="Enter your current password" name="user_password" autocomplete="off">
  </div>
</div>
<div class="form-group">
  <label for="inputNewPassword" class="col-lg-2 control-label">New Password:</label>
  <div class="col-lg-10">
    <input type="password" class="form-control" id="inputNewPassword" placeholder="Enter a new password" name="user_new_password" autocomplete="off">
  </div>
</div>
<div class="form-group">
  <label for="inputConfirmPassword" class="col-lg-2 control-label">Confirm Password:</label>
  <div class="col-lg-10">
    <input type="password" class="form-control" id="inputConfirmPassword" placeholder="Confirm your new password" name="user_confirm_password" autocomplete="off">
  </div>
</div>
<br />
<div class="form-group">
  <div class="col-lg-10 col-lg-offset-2">
    <img src="<?php echo $builder->inline(); ?>" />
  </div>
</div>
<div class="form-group">
  <label for="inputCaptcha" class="col-lg-2 control-label">Verification Code:</label>
  <div class="col-lg-10">
    <input type="text" class="form-control" id="inputCaptcha" placeholder="Enter the verification code" name="user_captcha" autocomplete="off">
  </div>
</div>
<br />
<div class="form-group">
  <div class="col-lg-10 col-lg-offset-2">
    <button type="submit" class="btn btn-primary" name="changepw" value="Change Password">Change Password</button>
    <button class="btn btn-default" type="reset">Reset</button>
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