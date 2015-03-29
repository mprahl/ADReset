<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'New Local Administrator';
    require_once(RESOURCE_DIR . 'templates/header.php');
?>
<!-- Content Starts -->
<body>
<!-- Navigation Menu Starts -->
<?php 
    if (LoginCheck::isLocal()) {
        require_once(RESOURCE_DIR . 'templates/local_navigation.php');
    }
    else {
        require_once(RESOURCE_DIR . 'templates/admin_navigation.php');
    }
?>
<!-- Navigation Menu Ends -->
<div class="container" id="mainContentBody">

<!-- Start of form inspired from http://bootswatch.com/flatly/ -->
<div class="col-md-12">
    <form class="form-horizontal" method="post" action="newlocaladmin.php" name="registerform">
      <fieldset>
        <h2 class="topHeader">New Local Account</h2>
        <div class="col-md-12">
            <?php
                // Show potential feedback from the register object
                if (flashMessage::flashIsSet('RegisterError')) {
                    FlashMessage::displayFlash('RegisterError', 'error');
                }
                elseif (flashMessage::flashIsSet('RegisterSuccess')) {
                    FlashMessage::displayFlash('RegisterSuccess', 'message');
                }
                else {
            ?>
                <div class="col-md-12">
                    <div class="alert alert-info infoBlurb" role="alert">
                        <a href="#" class="close" data-dismiss="alert">&times;</a>
                        <p >A local adminsitrator account is used to manage connection and system settings only.</p>
                        <p>This is so that in the event of a misconfiguration, there is a way to administer system settings without manually editing the database.</p>
                    </div>
                </div>
            <?php
                }
            ?>
        </div>

        <div class="form-group">
            <label for="login_input_username" class="col-lg-2 control-label">Username:</label>
            <div class="col-lg-10">
                <input id="login_input_username" class="form-control" type="text" pattern="^[a-zA-Z0-9]*[_.-]?[a-zA-Z0-9]*$" name="user_name" value="" required/>
            </div>
        </div>

        <div class="form-group">
            <label for="login_input_fullname" class="col-lg-2 control-label">Name:</label>
            <div class="col-lg-10">
                <input id="login_input_fullname" class="form-control" type="text" name="user_fullname" value="" required />
            </div>
        </div>

        <div class="form-group">
            <label for="login_input_email" class="col-lg-2 control-label">Email:</label>
            <div class="col-lg-10">
                <input id="login_input_email" class="form-control" type="email" name="user_email" value="" required />
            </div>
        </div>

        <div class="form-group">
            <label for="login_input_password_new" class="col-lg-2 control-label">Password:</label>
            <div class="col-lg-10">
                <input id="login_input_password_new" class="form-control" type="password" name="user_password_new" pattern=<?php echo '".{', Config::get('security/passwordLength'), ',}"' ?> placeholder=<?php echo '"Password with ', Config::get('security/passwordLength'), ' or more characters"' ?> required autocomplete="off" />
            </div>
        </div>

        <div class="form-group">
            <label for="login_input_password_repeat" class="col-lg-2 control-label">Repeat Password:</label>
            <div class="col-lg-10">
                <input id="login_input_password_repeat" class="form-control" type="password" name="user_password_repeat" pattern=<?php echo '".{', Config::get('security/passwordLength'), ',}"' ?> placeholder=<?php echo '"Password with ', Config::get('security/passwordLength'), ' or more characters"' ?> required autocomplete="off" />
            </div>
        </div>
        <br />
        <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
                <input type="submit" class="btn btn-primary" name="register" value="Create" />
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
