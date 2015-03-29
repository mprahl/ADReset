<?php
	require_once('../resources/functions/installerFunctions.php');

	// If init.php exists and there is a local user, then that means the installation was successful. If that is the case, redirect them to index.php
	try {
		if (file_exists('../resources/core/init.php')) {
			require_once('../resources/core/init.php');
			if ($db_connection = startPDOConnection()) {
				$stmt = $db_connection->query('SELECT NULL FROM localusers');
				if ($stmt->rowCount() != 0) {
					header('location: /index.php');
					exit();
				}
			}
		}
	}
	catch (Exception $e) {
		// Do nothing because it means that the configuration hasn't completed yet.
	}

?>

<!DOCTYPE html>
<?php
$pageTitle = 'Install ADReset';
    require_once('../resources/templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
	require_once('../resources/templates/installer_navigation.php');
?>
<!-- Navigation Menu Ends -->

<div class="container" id="mainContentBody">
	<div class="col-md-12">
      	<h2 class="topHeader">Install ADReset</h2>
      	<br />
		<div class="col-md-12">
			<h3>PHP Modules:</h3>
			<?php
				// Check if the proper PHP modules are present and check the PHP version
				$prerequisitesMet = true;
				$missingModules = checkModulesRequired();
				if (!empty($missingModules)) {
					$prerequisitesMet = false;
					echo '<p>The following modules need to be installed:<br />';
					echo '<ul>';
					foreach ($missingModules as $module) {
						echo '<li>' . $module . '</li>';
					}
					echo '</ul></p>';
				}
				else {
					echo '<p>All the required PHP Modules are installed.</p>';
				}

				// If all the PHP modules are present and the config file (init.php) hasn't been created yet, then let the user create it
				$connectionSuccessful = false;
				if ($prerequisitesMet && !file_exists('../resources/core/init.php')) {
					if (isset($_POST['connect'], $_POST['db_hostname'], $_POST['db_dbname'], $_POST['db_username'], $_POST['db_password'])) {
						echo '<br /><h3>Database Connection Status:</h3><br />';
						try {
							$db_connection = new PDO('mysql:host=' . $_POST['db_hostname'] . ';dbname=' . $_POST['db_dbname'], $_POST['db_username'], $_POST['db_password']);
							$connectionSuccessful = true;
							$db_connection = null;
							echo '<p>The database connection was successful. Now please proceed to create a local account.<br /></p>';
						}
						catch (PDOException $e) {
							echo '<p>The database connection failed with the following error:<br />', $e->getMessage(), '</p>';
						}
					}
					// If the post is not set, then display the Database Settings form
					else {
			?>
						<form class="form-horizontal" method="post" action="installer.php" name="installerForm">
						    <fieldset>
							<br /><h3>Database Settings:</h3></br />
							<div class="form-group">
					            <label for="db_hostname" class="col-lg-2 control-label">Hostname/IP Address:</label>
					            <div class="col-lg-10">
					                <input id="db_hostname" class="form-control" type="text" name="db_hostname" required/>
					            </div>
					        </div>

					        <div class="form-group">
					            <label for="db_dbname" class="col-lg-2 control-label">Database Name:</label>
					            <div class="col-lg-10">
					                <input id="db_dbname" class="form-control" type="text" name="db_dbname" required/>
					            </div>
					        </div>

							<div class="form-group">
					            <label for="db_username" class="col-lg-2 control-label">Username:</label>
					            <div class="col-lg-10">
					                <input id="db_username" class="form-control" type="text" name="db_username" required/>
					            </div>
					        </div>

					        <div class="form-group">
					            <label for="db_password" class="col-lg-2 control-label">Password:</label>
					            <div class="col-lg-10">
					                <input id="db_password" class="form-control" type="password" name="db_password" required autocomplete="off" />
					            </div>
					        </div>
					        <br />
					        <div class="form-group">
					            <div class="col-lg-10 col-lg-offset-2">
					                <input type="submit" class="btn btn-primary" name="connect" value="Connect" />
					                <button class="btn btn-default" type="reset">Reset</button>
					            </div>
					        </div>
					      </fieldset>
					    </form>
			<?php
					}
				}

				// If all the PHP modules were installed and the database connection settings were set in the form, then write it to init.php
				if ($prerequisitesMet && $connectionSuccessful) {
					// Create init.php
					$initPHP = fopen("../resources/core/init.php", "w") or die("<p>Unable to write to resources/core/init.php. Make sure ADReset has write permissions to this directory.</p>");
					$initPHPSettings = generateInit($_POST['db_hostname'], $_POST['db_username'], $_POST['db_password'], $_POST['db_dbname']);
					if (!empty($initPHPSettings)) {
						if (!fwrite($initPHP, $initPHPSettings)) {
							fclose($initPHP);
							die('Could not write to resources/core/init.php. Please make sure the webserver has right privileges on the core directory.');
						}
						fclose($initPHP);
					}
					else {
						die('Unexpected error: The init.php settings couldn\'t be generated. Please try again.');
					}
				}

				// Now that the PHP modules and init.php is created, it's time to import the database
				$dbStructureCreated = false;
				if ($prerequisitesMet && file_exists('../resources/core/init.php'))
				{
					require_once('../resources/core/init.php');
					if ($db_connection = startPDOConnection()) {
						if (file_exists('../resources/core/structure.sql')) {
							$sql = file_get_contents('../resources/core/structure.sql');
							try {
								$db_connection->exec($sql);
								$dbStructureCreated = true;
							}
							catch (PDOException $e) {
								die('ADReset failed to create the table structure with the following error' . $e->getMessage() . '<br />Please refresh the page to try again.');
							}
						}
						else {
							die('resources/core/structure.sql is missing and is required to continue with the installer.');
						}
					}
					else {
						die('The connection to the database failed. Please delete resources/core/init.php and restart the installer.');
					}

					// If the PHP modules are installed and the database Structure was created, then let them create a Local Administrator Account
					if ($prerequisitesMet && $dbStructureCreated)
					{						
						if (isset($_POST["register"])) {
							$registration = new NewLocalAdmin();
					        $registration->registerNewUser();
					    }
			?>
				
						<br /><h3>New Local Administrator:</h3><br />
						<form class="form-horizontal" method="post" action="installer.php" name="registerform">
					      <fieldset>
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
					                        <p >A local adminsitrator account is used to manage connection and system settings.</p>
					                        <p>Once this is created, you may login and configure the rest of ADReset from localadmin.php.</p>
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
			<?php
					}
				}
			?>
		</div>
	</div>
</div>
<!-- Content Ends -->
</body>
</html>
