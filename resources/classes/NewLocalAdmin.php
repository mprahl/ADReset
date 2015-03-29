<?php
    require_once(__DIR__ . '/../core/init.php');
    //Function to check if the password conforms to the security policy
    require_once(RESOURCE_DIR . 'functions/passwordPolicyMatch.php');

    // This class is used to create a new administrative account. This class was inspired by https://github.com/panique/php-login-minimal
    class NewLocalAdmin {
        // The database connection object
        private $db_connection = null;

        public function __construct() {
            // Make sure the database can connect
            if (!$this->db_connection = startPDOConnection()) {
                echo '<h2 style="text-align:center">Database Connection Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
                Logger::log('error', 'The database connection failed');
                die();
            }
        }

        protected function setErrorAndQuit($message) {
            if (isset($message)) {
                FlashMessage::flash('RegisterError', $message);
                header('Location: /installer.php');
                exit();
            }
        }

        // This function handles the entire registration process. It checks all error possibilities and creates a new administrator in the database if the input passes
        public function registerNewUser($user_group = 1) {
            if (empty($_POST['user_name'])) {
                $this->setErrorAndQuit('Username cannot be empty.');
            }
            elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
                $this->setErrorAndQuit('Password cannot be empty.');
            }
            elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
                $this->setErrorAndQuit('Passwords do not match.');
            }
            elseif (!passwordPolicyMatch($_POST['user_password_new'])) {
                $this->setErrorAndQuit('Password does not conform to the password policy.<br />'. passwordPolicyWritten());
            }
            elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
                $this->setErrorAndQuit('Password does not conform to the password policy.<br />'. passwordPolicyWritten());
            }
            elseif (!preg_match('/^[a-zA-Z0-9]*[_.-]?[a-zA-Z0-9]*$/', $_POST['user_name'])) {
                $this->setErrorAndQuit('Username does not match the naming scheme. Only letters, numbers, underscores, and periods are allowed');
            }
            elseif (empty($_POST['user_email'])) {
                $this->setErrorAndQuit('Email cannot be empty.');
            }
            elseif (strlen($_POST['user_email']) > 64) {
                $this->setErrorAndQuit('Email cannot be longer than 64 characters.');
            }
            elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
                $this->setErrorAndQuit('Your email address is not in a valid email format.');
            }
            elseif (!empty($_POST['user_name'])
                && strlen($_POST['user_name']) <= 64
                && strlen($_POST['user_name']) >= 2
                && preg_match('/^[a-zA-Z0-9]*[_.-]?[a-zA-Z0-9]*$/', $_POST['user_name'])
                && !empty($_POST['user_email'])
                && strlen($_POST['user_email']) <= 64
                && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
                && !empty($_POST['user_password_new'])
                && !empty($_POST['user_password_repeat'])
                && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
            ) {
                if ($this->db_connection = startPDOConnection()) {

                    //Trim the whitespace
                    $user_name = trim($_POST['user_name']);
                    $user_fullname = trim($_POST['user_fullname']);
                    $user_email = trim($_POST['user_email']);
                    $user_password = $_POST['user_password_new'];
                    $user_created = date('Y-m-d H:i:s');

                    // crypt the user's password with PHP 5.5's password_hash() function
                    $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

                    // Check if the user/email address is already taken or not
                    if ($stmt = $this->db_connection->prepare('SELECT null FROM localusers WHERE username=? OR email=?')) {
                        
                        if ($stmt->execute(array($user_name, $user_email))) {
                            // Make sure that the user does not exist
                            if ($stmt->rowCount() != 0) {
                                $this->setErrorAndQuit('Sorry, that username or email address is already taken.');
                            }
                            else {
                                $stmt = null;
                                // Prepare and bind the database to insert the administrator account
                                if ($stmt = $this->db_connection->prepare('INSERT INTO localusers (username, password, email, name, created) VALUES (?, ?, ?, ?, ?)')) {
                                    
                                    if ($stmt->execute(array($user_name, $user_password_hash, $user_email, $user_fullname, $user_created))) {
                                        $stmt = null;
                                        Logger::log('audit', 'New Local Admin Success: The local administrator "' . $user_name . '" was created');
                                        FlashMessage::flash('RegisterSuccess', $user_name . ' has been created successfully.<br />Make sure that you delete public/installer.php.');
                                        header('Location: /localadmin.php');
                                        exit();
                                    }
                                    else {
                                        $stmt = null;
                                        Logger::log('error', 'The local administrator "' . $user_name . '" could not be created due to a database error');
                                        $this->setErrorAndQuit('Sorry, the new account creation failed.<br />Please go back and try again.');
                                    } 
                                }
                                else {
                                    Logger::log('error', 'The local administrator "' . $user_name . '" could not be created due to a database error');
                                    $this->setErrorAndQuit('Sorry, the new account creation failed.<br />Please go back and try again.');
                                }
                            }
                        }
                        else {
                            Logger::log('error', 'The local administrator "' . $user_name . '" could not be created due to a database error');
                            $this->setErrorAndQuit('There was a problem connecting to the database.<br />Please try again.');
                        }
                    }
                    else {
                        Logger::log('error', 'The local administrator "' . $user_name . '" could not be created due to a database error');
                        $this->setErrorAndQuit('There was a problem connecting to the database.<br />Please try again.');
                    }
                } 
                else {
                    Logger::log('error', 'The database connection failed');
                    $this->setErrorAndQuit('There was a problem connecting to the database.<br />Please try again.');
                }
            } 
            else {
                $this->setErrorAndQuit('Sorry, your registration failed.<br />Please go back and try again.');
            }
        }
    }
