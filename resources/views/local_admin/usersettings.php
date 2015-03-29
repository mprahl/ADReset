<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Settings';
	require_once(RESOURCE_DIR . 'templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
	require_once(RESOURCE_DIR . 'templates/local_navigation.php');
?>
<!-- Navigation Menu Ends -->
<!-- Content Starts -->
<div class="container" id="mainContentBody">
	<h2 class="topHeader">Settings</h2>
	<h3>Welcome <?php echo ucwords($_SESSION['user_name']); ?>,</h3>
	<br />
	<div class="col-md-12">
	    <?php
	        // Show potential feedback from the settings changes object
            if (flashMessage::flashIsSet('ChangePWError')) {
                FlashMessage::displayFlash('ChangePWError', 'error');
            }
            elseif (flashMessage::flashIsSet('ChangePWMessage')) {
                FlashMessage::displayFlash('ChangePWMessage', 'message');
            }
            elseif (flashMessage::flashisSet('ChangeProfileMessage')) {
            	FlashMessage::displayFlash('ChangeProfileMessage', 'message');
            }
            elseif (flashMessage::flashisSet('ChangeProfileError')) {
            	FlashMessage::displayFlash('ChangeProfileError', 'error');
            }
	    ?>
	</div>
    <h4>What would you like to change?</h4>
	<div class="panel-group" id="accordion">
		<!-- Change Password Form -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
        			<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Change Password</a>
        		</h4>
      		</div>
        	<div id="collapse1" class="panel-collapse collapse">
            	<div class="panel-body">
    		    <form class="form-horizontal" method="post" action="/settings/localusersettings.php" name="loginform">
					<fieldset>
			        <div class="form-group">
			        	<label for="inputOldPassword" class="col-lg-2 control-label">Old Password:</label>
			        	<div class="col-lg-10">
			            	<input type="password" class="form-control" id="inputOldPassword" placeholder="Old Password" name="user_oldpassword" value="" autocomplete="off">
			        	</div>
			        </div>
			        <div class="form-group">
			        	<label for="inputNewPassword" class="col-lg-2 control-label">New Password (<?php echo Config::get('security/passwordLength') ?>+ Characters):</label>
			        	<div class="col-lg-10">
			            	<input type="password" class="form-control" id="inputNewPassword" placeholder="New Password" name="user_newpassword" pattern=<?php echo '".{', Config::get('security/passwordLength'), ',}"' ?> value="" autocomplete="off">
			        	</div>
			        </div>
			        <div class="form-group">
			        	<label for="inputConfirmNewPassword" class="col-lg-2 control-label">Confirm New Password:</label>
			        	<div class="col-lg-10">
			            	<input type="password" class="form-control" id="inputConfirmNewPassword" placeholder="New Password" name="user_confirmnewpassword" pattern=<?php echo '".{', Config::get('security/passwordLength'), ',}"' ?> value="" autocomplete="off">
			        	</div>
			        </div>
			        <br />
			        <div class="form-group">
			        	<div class="col-lg-10 col-lg-offset-2">
			        		<button type="submit" class="btn btn-primary" name="changePassword" value="Change Password">Update</button>
			        		<button class="btn btn-default" type="reset">Reset</button>
			        	</div>
			        </div>
			    </fieldset>
			    </form>
				</div>
         	</div>
    	</div>

    	<!-- Change Profile Form -->
		<div class="panel panel-default">
    		<div class="panel-heading">
            	<h4 class="panel-title">
            		<a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Change Profile</a>
            	</h4>
      		</div>
        	<div id="collapse2" class="panel-collapse collapse">
            	<div class="panel-body">
    		    <form class="form-horizontal" method="post" action="/settings/localusersettings.php" name="loginform">
					<fieldset>
					<div class="form-group">
			        	<label for="inputNewUsername" class="col-lg-2 control-label">Username:</label>
			        	<div class="col-lg-10">
			            	<input type="text" class="form-control" id="inputNewUsername" placeholder="New Username" name="user_newusername" value=<?php echo '"' . sanitize($_SESSION['user_name']) . '"';?> autocomplete="off">
			        	</div>
			        </div>
			        <div class="form-group">
			        	<label for="inputNewName" class="col-lg-2 control-label">Name:</label>
			        	<div class="col-lg-10">
			            	<input type="text" class="form-control" id="inputNewName" placeholder="New Name" name="user_newname" value=<?php echo '"' . sanitize($userInfo->get()['name']) . '"';?> autocomplete="off">
			        	</div>
			        </div>
			        <div class="form-group">
			        	<label for="inputNewEmail" class="col-lg-2 control-label">Email:</label>
			        	<div class="col-lg-10">
			            	<input type="text" class="form-control" id="inputNewEmail" placeholder="New Email" name="user_newemail" value=<?php echo '"' . sanitize($userInfo->get()['email']) . '"';?> autocomplete="off">
			        	</div>
			        </div>
			        <br />
			        <div class="form-group">
			        	<div class="col-lg-10 col-lg-offset-2">
			        		<button type="submit" class="btn btn-primary" name="changeProfile" value="Change Profile">Update</button>
			        		<button class="btn btn-default" type="reset">Reset</button>
			        	</div>
			        </div>
			    </fieldset>
			    </form>
				</div>
          	</div>
    	</div>

    	<!-- Change ..... Form -->
		<div class="panel panel-default">
    		<div class="panel-heading">
            	<h4 class="panel-title">
            		<a data-toggle="collapse" data-parent="#accordion" href="#collapse3">Security Settings</a>
            	</h4>
      		</div>
        	<div id="collapse3" class="panel-collapse collapse">
	            <div class="panel-body">
	            	No security settings are configurable yet.
	        	</div>
          	</div>
    	</div>

  	</div> 

</div>
<!-- Content Ends -->
</body>
</html>