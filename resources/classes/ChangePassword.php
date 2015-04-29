<?php
    //Require init.php which is used to declare the Global config array. This is also used to include functions that are used on most pages.
    require_once(__DIR__ . '/../core/init.php');
    //Function to check if the password conforms to the security policy
    require_once(RESOURCE_DIR . 'functions/passwordPolicyMatch.php');
    
    // This is a class used to change the administrator account password
    Class ChangePassword {
        // The database connection object
        private $db_connection = null;

        public function __construct() {
            // On construction of the  object, simply run the changeThePassword function
            $this->changeThePassword();
        }

        public function __destruct() {
            $this->db_connection = null;
        }

        protected function setErrorAndQuit($message) {
            if (isset($message)) {
                FlashMessage::flash('ChangePWError', $message);
                header('Location: /settings/localusersettings.php');
                exit();
            }
            else {
                header('Location: /settings/localusersettings.php');
                exit();
            }
        }

        private function changeThePassword() {
            // If the POST data of user_oldpassword doesn't exist, store a flash message in the user's session and redirect them to the usersettingspage
            if (!isset($_POST['user_oldpassword'])) {
                $this->setErrorAndQuit('Old Password field was empty.');
            }
            // If the POST data of user_newpassword doesn't exist, store a flash message in the user's session and redirect them to the usersettingspage
            elseif (!isset($_POST['user_newpassword'])) {
                $this->setErrorAndQuit('New Password field was empty.');
            }
            // If the POST data of user_confirmnewpassword doesn't exist, store a flash message in the user's session and redirect them to the usersettingspage
            elseif (!isset($_POST['user_confirmnewpassword'])) {
                $this->setErrorAndQuit('Confirm New Password field was empty.');
            }
            // If the POST data of user_oldpassword is not equal to user_newpassword, store a flash message in the user's session and redirect them to the usersettingspage
            elseif ($_POST['user_oldpassword'] == $_POST['user_newpassword']) {
                $this->setErrorAndQuit('Old Password and New Password are the same.');
            }
            // If the POST data of user_newpassword is not equal to user_confirmnewpassword, store a flash message in the user's session and redirect them to the usersettingspage
            elseif ($_POST['user_newpassword'] != $_POST['user_confirmnewpassword']) {
                $this->setErrorAndQuit('Passwords do not match.');
            }
            // If the POST data of user_newpassword doesn't pass the passwordPolicyMatch function, store a flash message in the user's session and redirect them to the usersettingspage
            elseif (!passwordPolicyMatch($_POST['user_newpassword'])) {
                $this->setErrorAndQuit('Password does not conform to the password policy.<br />'. passwordPolicyWritten());
            }
            // If all the required POST values that are required exist, then proceed with the password change
            elseif (isset($_POST['user_oldpassword']) && isset($_POST['user_newpassword']) && !empty($_POST['user_confirmnewpassword'])) {
                // If the database connection was successful, then continue on
                if ($this->db_connection = startPDOConnection()) {
                    // Trim any whitespace, saniziting the data is not required because prepared statements are used
                    $user_oldpassword = trim($_POST['user_oldpassword']);

                    // A database query to get all user info of the logged in administrator
                    $stmt = $this->db_connection->prepare('SELECT username, email, password FROM localusers WHERE username = ?');
                    $stmt->execute(array($_SESSION['user_name']));

                    // If the administrator exists then continue on with the password change
                    if ($stmt->rowCount()== 1) {
                        // Get the results as an array so the password can be queried from the user
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        $stmt = null;

                        // Use PHP 5.5's password_verify() function to check if the provided password equals hash of the administrator's password
                        if (password_verify($_POST['user_oldpassword'], $user['password'])) {
                            $user_newpassword_hash = password_hash($_POST['user_newpassword'], PASSWORD_DEFAULT);
                            
                            // Send the prepared statement to the database
                            $stmt = $this->db_connection->prepare('UPDATE localusers SET password = ? WHERE username = ?');
                             
                            // If the administrator's password update successfully, then close the database connection and let the adminsitrator know
                            if ($stmt->execute(array($user_newpassword_hash, $_SESSION['user_name']))) {
                                //Close the connection
                                $stmt = null;
                                Logger::log ('audit', 'Local Admin Password Change: The local administrator "' . $_SESSION['user_name'] . '" successfully changed their password');
                                FlashMessage::flash('ChangePWMessage', 'Your password was changed successfully.');
                                header('Location: /settings/localusersettings.php');
                                exit();
                            }
                            // If the adminsitrator's password update failed, then close the database connection and let the administrator know
                            else {
                                //Close the connection
                                $stmt = null;
                                Logger::log ('error', 'Password change for the local administrator "' . $SESSION['user_name'] . '" failed because the database couldn\'t execute the query');
                                $this->setErrorAndQuit('Sorry, your password update failed. Please go back and try again.');
                            } 
                        }
                        else {
                            Logger::log ('audit', 'Local Admin Password Change Failure: The local administrator "' . $_SESSION['user_name'] . '" failed to change their password because the current password supplied was incorrect');
                            // If the adminsitrator's password was incorrectly entered and let the administrator know
                            $this->setErrorAndQuit('The password entered is incorrect.<br />Please try again.');
                        }
                    }

                    else {
                        Logger::log ('error', 'The local administrator password change failed because the user "' . $_SESSION['user_name'] . '" does not exist in the database');
                        $this->setErrorAndQuit('The user does not exist in the database.<br />Please logout and log back in.');
                    }
                }
                else {
                    Logger::log ('error', 'The local administrator password change failed for user "' . $_SESSION['user_name'] . '" because the database connection failed');
                    $this->setErrorAndQuit('There was a problem connecting to the database.<br />Please try again.');
                }
            }
        }

    }
