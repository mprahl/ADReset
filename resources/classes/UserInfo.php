<?php
	require_once(__DIR__ . '/../core/init.php');

	Class UserInfo {

		private $db_connection = null;

		public function __construct() {
			// Make sure the database can connect
			if (!$this->db_connection = startPDOConnection()) {
				echo '<h2 style="text-align:center">Database Connection Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
				die();
			}
		}

		public function __destruct() {
			$this->db_connection = null;
		}

		protected function setErrorAndQuit($message) {
	        if (isset($message)) {
	            FlashMessage::flash('ChangeProfileError', $message);
	            header('Location: /settings/localusersettings.php');
	            exit();
	        }
	    }

		public function get($user_username = ''){
			if (empty($user_username)) {
				if (isset($_SESSION['user_name'])) {
					$user_username = isset($_SESSION['user_name']);
				}
				else {
					return array();
				}
			}

            if (isset($_SESSION['user_name']) && $this->db_connection) {
            	$user_username = $_SESSION['user_name'];
				$stmt = $this->db_connection->prepare('SELECT username, email, name, created FROM localusers WHERE username = ?');
				
				if ($stmt->execute(array($user_username))) {
		            // If this user exists return the results
		            if ($stmt->rowCount() == 1) {
		            	$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
		            	//Close the connection
	                    $stmt = null;
		            	return $userInfo;
		            }
		            else {
		            	return array();
		            }
		        }
        	}
        	else {
                return array();
        	}
		}

		public function setProfile($user_username = '') {
			if (empty($user_username)) {
				if (isset($_SESSION['user_name'])) {
					$user_username = isset($_SESSION['user_name']);
				}
				else {
					return false;
				}
			}

            if ($this->db_connection) {
				$user_newusername = trim($_POST['user_newusername']);
				$user_name = trim($_POST['user_newname']);
				$user_email = trim($_POST['user_newemail']);

				if ($this->get($user_username)['username'] != $user_newusername){
					$stmt = $this->db_connection->prepare('SELECT username FROM localusers WHERE username = ?');
	                
	                if($stmt->execute(array($user_newusername))) {
		                // if this username is already taken
		                if ($stmt->rowCount() != 0) {
		                	//Close the connections
			                $stmt = null;
	                    	FlashMessage::flash('ChangeProfileError', 'This username is already taken.');
				            header('Location: /settings/localusersettings.php');
				            exit();
	                    }
	                }
	                $stmt = null;
				}
				
				if ($this->get($user_username)['email'] != $user_email){
					$stmt = $this->db_connection->prepare('SELECT email FROM localusers WHERE email = ?');
	                
	                if ($stmt->execute(array($user_email))) {
		                // if this username is already taken
		                if ($stmt->rowCount() != 0) {
		                	//Close the connections
			                $stmt = null;
			                $this->setErrorAndQuit('This email address is already taken.');
	                    }
	                }
	                $stmt = null;
				}

				if (strlen($user_newusername) > 64 || strlen($user_newusername) < 2) {
					$this->setErrorAndQuit('Password does not conform to the password policy.<br />'. passwordPolicyWritten());
		        }
		        elseif (!preg_match('/^[a-zA-Z0-9]*[_.-]?[a-zA-Z0-9]*$/', $user_newusername)) {
		        	$this->setErrorAndQuit('Username does not match the naming scheme. Only letters, numbers, underscores, and periods are allowed');
		        }
		        elseif (empty($user_email)) {
		        	$this->setErrorAndQuit('Email cannot be empty.');
		        }
		        elseif (strlen($user_email) > 64) {
		        	$this->setErrorAndQuit('Email cannot be longer than 64 characters.');
		        }
		        elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
		        	$this->setErrorAndQuit('Your email address is not in a valid email format.');
		        }
		        elseif (!empty($user_newusername)
		            && strlen($user_newusername) <= 64
		            && strlen($user_newusername) >= 2
		            && preg_match('/^[a-zA-Z0-9]*[_.-]?[a-zA-Z0-9]*$/', $user_newusername)
		            && !empty($user_email)
		            && strlen($user_email) <= 64
		            && filter_var($user_email, FILTER_VALIDATE_EMAIL)
		        ) {
					$user_name = $_POST['user_newname'];
					$user_email = $_POST['user_newemail'];
					$stmt = $this->db_connection->prepare('UPDATE localusers SET username = ?, name = ?, email = ? WHERE username = ?');
					
					if ($stmt->execute(array($user_newusername, $user_name, $user_email, $user_username))) {
						$_SESSION['user_name'] = $user_newusername;
						Logger::log('audit', 'Local Admin Profile Change Success: Profile of User "' . $user_username . '" was set to User "' . $user_newusername . '", Name "' . $user_name . '", Email "' . $user_email . '"');
				        FlashMessage::flash('ChangeProfileMessage', 'Profile was successfully modified.');
					    header('Location: /settings/localusersettings.php');
					    exit();
					}
					else {
						$this->setErrorAndQuit('Profile could not be modified.');
					}
				}
			}
			else {
				$this->setErrorAndQuit('There was a problem connecting to the database. Please try again.');
			}
		}

	}
