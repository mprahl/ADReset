<!DOCTYPE html>
<?php
$pageTitle = 'Log In';
    require_once(RESOURCE_DIR . 'templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
    require_once(RESOURCE_DIR . 'templates/not_loggedin_navigation.php');
?>
<!-- Navigation Menu Ends -->
<div class="container" id="mainContentBody">
    <div class="col-md-12">
        <form class="form-horizontal" method="post" action="localadmin.php" name="loginform">
          <fieldset>
            <h2 class="topHeader">Local Administrator Login</h2>
    <div class="col-md-12">
        <?php
            // Show potential feedback from the login object
            if (flashMessage::flashIsSet('LoginError')) {
                FlashMessage::displayFlash('LoginError', 'error');
            }
            elseif (flashMessage::flashIsSet('LoginMessage')) {
                FlashMessage::displayFlash('LoginMessage', 'message');
            }
            elseif (flashMessage::flashIsSet('RegisterSuccess')) {
                FlashMessage::displayFlash('RegisterSuccess', 'message');
            }
        ?>
    </div>
            <div class="form-group">
              <label for="inputUsername" class="col-lg-2 control-label">Username:</label>
              <div class="col-lg-10">
                <input type="text" class="form-control" id="inputUsername" placeholder="Username" name="user_name">
              </div>
            </div>
            <div class="form-group">
              <label for="inputPassword" class="col-lg-2 control-label">Password:</label>
              <div class="col-lg-10">
                <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="user_password" autocomplete="off">
              </div>
            </div>
            <br />
            <div class="form-group">
              <div class="col-lg-10 col-lg-offset-2">
                <button type="submit" class="btn btn-primary" name="login" value="Log in">Submit</button>
                <button class="btn btn-default" type="reset">Reset</button>
              </div>
            </div>
          </fieldset>
        </form>
    </div>
</div>
</body>
</html>