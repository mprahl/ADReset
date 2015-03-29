<?php
	require_once(__DIR__ . '/../core/init.php');

	Class SystemSettings {

		private $db_connection;
		private $errors = array();
		private $emailSettingsErrorMsg = array();

		public function __construct() {
			if (!$this->db_connection = startPDOConnection()) {
				echo '<h2 style="text-align:center">Database Connection Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
				die();
			}
		}

		public function __destruct() {
			$this->db_connection = null;
		}

		private function addAdminGroup($groupSAM) {
			if (isset($groupSAM)) {
				try {
					$AD = new AD();
				}
				catch(Exception $e) {
					Logger::log('error', $e . ' when attempting to add the new administrator group ' . $groupSAM . '.');
					throw new Exception($e);
				}

				if(!empty($groupResult = $AD->getGroup($groupSAM))) {
					if (isset($groupResult['objectguid'])) {
						$stmt = $this->db_connection->prepare('SELECT null FROM admingroups WHERE groupguid = ?');
						if ($stmt->execute(array($groupResult['objectguid']))) {
							if ($stmt->rowCount() == 0) {
								$stmt = null;
								$stmt = $this->db_connection->prepare('INSERT INTO admingroups (groupguid) VALUES (?)');
								if ($stmt->execute(array($groupResult['objectguid']))) {
									if (isset($_SESSION['user_name'])) {
										Logger::log('audit', 'Add Administrator Group Success: The administrator group "' . $groupSAM . '"" was added by "' . $_SESSION['user_name'] . '"');
									}
									else {
										Logger::log('audit', 'Add Administrator Group Success: The administrator group "' . $groupSAM . '"" was added');
									}
									return true;
								}
							}
						}

						// If it didn't return true, then log an error and throw an exception
						Logger::log('error', 'The new administrator group could not be added due to a database error.');
						throw new Exception('There was a database error. Please try again.');
					}
				}
				else {
					throw new Exception('The group ' . $groupSAM . ' could not be found.');
				}
			}

			throw new Exception('The group SAMAccountName was not provided');
			return false;
		}

		private function deleteAdminGroup($groupGUID, $groupName = '') {
			if (isset($groupGUID)) {
				$stmt = $this->db_connection->prepare('DELETE FROM admingroups WHERE groupguid = ?');
				if ($stmt->execute(array($groupGUID))) {
					if ($stmt->rowCount() > 0) {
						if (isset($_SESSION['user_name'])) {
							if (!empty($groupName)) {
								Logger::log('audit', 'Delete Administrator Group Success: The administrator group "' . $groupName . '" was removed by "' . $_SESSION['user_name'] . '"');
							}
							else {
								Logger::log('audit', 'Delete Administrator Group Success: The administrator group "' . $groupGUID . '" was removed by "' . $_SESSION['user_name'] . '"');
							}
						}
						else {
							if (!empty($groupName)) {
								Logger::log('audit', 'Delete Administrator Group Success: The administrator group "' . $groupName . '" was removed');
							}
							else {
								Logger::log('audit', 'Delete Administrator Group Success: The administrator group "' . $groupGUID . '" was removed as an Adminsitrative group.');
							}
						}
						
						return true;
					}
				}

				// If it didn't return true, then log an error
				if (!empty($groupName)) {
					Logger::log('error', 'The administrator group "' . $groupName . '" could not be removed due to a database error.');
				}
				else {
					Logger::log('error', 'The administrator group "' . $groupGUID . '" could not be removed due to a database error.');
				}
				
				throw new Exception('The group couldn\'t be removed due to a database error.');
			}
			
			throw new Exception('The group GUID was not provided');
			return false;
		}

		public function addAdminGroupWithPost() {
			if (isset($_POST['groupname'])) {
				try {
					$this->addAdminGroup(html_entity_decode(trim($_POST['groupname'])));
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupname']) . ' was successfully added to the list of Administrative groups.');
					return true;		
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()));
					return false;
				}
			}
			
			FlashMessage::flash('SystemSettingsError', 'The administrator group couldn\'t be added');
			return false;
		}

		public function deleteAdminGroupWithPost() {
			if (isset($_POST['groupname']) && isset($_POST['groupguid'])) {
				try {
					$this->deleteAdminGroup($_POST['groupguid'], $_POST['groupname']);
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupname']) . ' was successfully removed. For affected logged in users, it will take effect once they log out and log back in.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($_POST['groupname']) . ' could not be removed from the Administrative groups. Please try again.');
				}
			}
			elseif (isset($_POST['groupguid'])) {
				try {
					$this->deleteAdminGroup($_POST['groupguid']);
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupguid']) . ' was successfully removed. For affected logged in users, it will take effect once they log out and log back in.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()) . ' Please try again.');
				}
			}

			return false;
		}

		public function getAdminGroups() {
			if ($stmt = $this->db_connection->query('SELECT groupguid FROM admingroups')) {
				if ($groups = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
					$stmt = null;

					try {
						$AD = new AD();
					}
					catch(Exception $e) {
						Logger::log('error', $e . ' when attempting to get the current administrator groups.');
						return array();
					}

					$groupsArray = null;
					$i = 0;
					foreach ($groups as $group) {
						if ($groupName = $AD->getGroupSAMFromGUID($group['groupguid'])) {
							$groupsArray[$i]['samaccountname'] = $groupName;	
						}

						$groupsArray[$i]['guid'] = $group['groupguid'];
						$i++;
					}

					if (isset($groupsArray)) {
						return $groupsArray;
					}
				}
			}

			return array();
		}

		private function addResetGroup($groupSAM) {
			if (isset($groupSAM)) {
				try {
					$AD = new AD();
				}
				catch(Exception $e) {
					Logger::log('error', $e . ' when attempting to add the new administrator group ' . $groupSAM . '.');
					throw new Exception($e);
					return false;
				}

				if(!empty($groupResult = $AD->getGroup($groupSAM))) {
					if (isset($groupResult['objectguid'])) {
						$stmt = $this->db_connection->prepare('SELECT null FROM resetgroups WHERE groupguid = ?');
						if ($stmt->execute(array($groupResult['objectguid']))) {
							if ($stmt->rowCount() == 0) {
								$stmt = null;
								$stmt = $this->db_connection->prepare('INSERT INTO resetgroups (groupguid) VALUES (?)');
								if ($stmt->execute(array($groupResult['objectguid']))) {
									if (isset($_SESSION['user_name'])) {
										Logger::log('audit', 'New Reset Group Success: The new reset group ' . $groupSAM . ' was added by "' . $_SESSION['user_name'] . '".');
									}
									else {
										Logger::log('audit', 'New Reset Group Success: The new reset group ' . $groupSAM . ' was added.');
									}
									return true;
								}
							}
						}

						// If it didn't retun true, then log the error and throw an exception
						Logger::log('error', 'The new reset group could not be added due to a database error.');
						throw new Exception('There was a database error. Please try again.');
					}
				}
				else {
					Logger::log('error', 'The group ' . $groupSAM . ' could not be found.');
					throw new Exception('The group ' . $groupSAM . ' could not be found.');
				}
			}

			throw new Exception('The group SAMAccountName was not provided');
			return false;
		}

		private function deleteResetGroup($groupGUID, $groupName = '') {
			if (isset($groupGUID)) {
				$stmt = $this->db_connection->prepare('DELETE FROM resetgroups WHERE groupguid = ?');
				if ($stmt->execute(array($groupGUID))) {
					if ($stmt->rowCount() > 0) {
						if (isset($_SESSION['user_name'])) {
							if (!empty($groupName)) {
								Logger::log('audit', 'Delete Reset Group Success: The reset group ' . $groupName . ' was removed by "' . $_SESSION['user_name'] . '".');
							}
							else {
								Logger::log('audit', 'Delete Reset Group Success: The reset group ' . $groupGUID . ' was removed by "' . $_SESSION['user_name'] . '".');
							}
						}
						else {
							if (!empty($groupName)) {
								Logger::log('audit', 'Delete Reset Group Success: The reset group ' . $groupName . ' was removed.');
							}
							else {
								Logger::log('audit', 'Delete Reset Group Success: The reset group ' . $groupGUID . ' was removed.');
							}
						}
						
						return true;
					}
				}

				if (!empty($groupName)) {
					Logger::log('error', 'The reset group ' . $groupName . ' could not be removed due to a database error.');
					throw new Exception('The reset group ' . $groupName . ' could not be removed due to a database error.');
				}
				else {
					Logger::log('error', 'The reset group ' . $groupGUID . ' could not be removed due to a database error.');
					throw new Exception('The reset group ' . $groupGUID . ' could not be removed due to a database error.');
				}
					
			}
			
			throw new Exception('The group GUID was not provided');
			return false;
		}

		public function addResetGroupWithPost() {
			if (isset($_POST['groupname'])) {
				try {
					$this->addResetGroup(html_entity_decode(trim($_POST['groupname'])));
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupname']) . ' was successfully added.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()));
					return false;
				}
			}

			FlashMessage::flash('SystemSettingsError', 'The group couldn\'t be added');
			return false;
		}

		public function deleteResetGroupWithPost() {
			
			if (isset($_POST['groupname']) && isset($_POST['groupguid'])) {
				try {
					$this->deleteResetGroup($_POST['groupguid'], $_POST['groupname']);
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupname']) . ' was successfully removed.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()) . ' Please try again.');
					return false;
				}
			}
			elseif (isset($_POST['groupguid'])) {
				try {
					$this->deleteResetGroup($_POST['groupguid']);
					FlashMessage::flash('SystemSettingsMessage', sanitize($_POST['groupguid']) . ' was successfully removed.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()) . ' Please try again.');
					return false;
				}
			}

			return false;
		}

		public function getResetGroups() {
			if ($stmt = $this->db_connection->query('SELECT groupguid FROM resetgroups')) {
				if ($groups = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
					$stmt = null;

					try {
						$AD = new AD();
					}
					catch(Exception $e) {
						Logger::log('error', $e . ' when attempting to get the current reset groups.');
						return array();
					}

					$groupsArray = null;
					$i = 0;
					foreach ($groups as $group) {
						if ($groupName = $AD->getGroupSAMFromGUID($group['groupguid'])) {
							$groupsArray[$i]['samaccountname'] = $groupName;	
						}

						$groupsArray[$i]['guid'] = $group['groupguid'];
						$i++;	
					}

					if (isset($groupsArray)) {
						return $groupsArray;
					}
				}
			}

			return array();
		}

		private function setEmailSetting($setting, $settingValue) {
			if (isset($setting) && isset($settingValue)) {
				if ($setting == 'password') {
					$settingValue = Crypto::cust_encrypt($settingValue);
				}

				$stmt = $this->db_connection->prepare('SELECT null FROM emailsettings WHERE setting = ?');
				if ($stmt->execute(array($setting))) {
					// If the setting is not in the database, then add it, if it is, then go to the elseif statement to update it
					if ($stmt->rowCount() == 0) {
						$stmt = null;
						$stmt = $this->db_connection->prepare('INSERT INTO emailsettings (setting, settingValue) VALUES (?, ?)');
						if ($stmt->execute(array($setting, $settingValue))) {
							// If the password was changed, don't show the encrypted password in the logs
							if ($setting == 'password') {
								Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set');
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set');
								}
							}
							else {
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set to ' . $settingValue .' by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set to ' . $settingValue);
								}
							}
							
							return true;
						}
					}
					elseif ($stmt->rowCount() >= 1) {
						$stmt = null;
						$stmt = $this->db_connection->prepare('UPDATE emailsettings SET settingvalue = ? WHERE setting = ?');
						if ($stmt->execute(array($settingValue, $setting))) {
							if ($setting == 'password') {
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set');
								}
							}
							else {
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set to ' . $settingValue .' by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Email Settings Change Success: The email setting of ' . $setting . ' was set to ' . $settingValue);
								}
							}
							return true;
						}
					}
				}

				Logger::log('error', 'The email setting ' . $setting . ' was not set due to a database error.');
			}

			return false;
		}

		private function setEmailSettings($fromEmail, $fromName, $username, $password, $server, $port, $encryption) {
			if (isset($fromEmail) && isset($fromName) && isset($username) && isset($password) && isset($server) && isset($port) && isset($encryption)) {
				$errorCollector = array();

				if (!$this->setEmailSetting('fromEmail', $fromEmail)) {
					$errorCollector[] = 'The fromEmail could not be set.';
				}

				if (!$this->setEmailSetting('fromName', $fromName)) {
					$errorCollector[] = 'The fromName could not be set.';
				}

				if (!$this->setEmailSetting('username', $username)) {
					$errorCollector[] = 'The username could not be set.';
				}

				if (!$this->setEmailSetting('password', $password)) {
					$errorCollector[] = 'The password could not be set.';
				}

				if (!$this->setEmailSetting('server', $server)) {
					$errorCollector[] = 'The password could not be set.';
				}

				if (!$this->setEmailSetting('port', $port)) {
					$errorCollector[] = 'The port could not be set.';
				}

				if (!$this->setEmailSetting('encryption', $encryption)) {
					$errorCollector[] = 'The encryption could not be set.';
				}

				if (!empty($errorCollector)) {
					$errorMessage = 'The following were not set due to database errors:<br />';
					foreach ($errorCollector as $error) {
						$errorMessage += $error . '<br />';
					}

					$this->emailSettingsErrorMsg = $errorMessage;
					return false;
				}
				else {
					return true;
				}
			}
		}

		public function getEmailSettings() {
			$settingsArray = array();
			$stmt = $this->db_connection->query('SELECT setting, settingValue FROM emailsettings');
			if ($results = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($results as $result) {
					if ($result['setting'] == 'password') {
						$result['settingValue'] = Crypto::cust_decrypt($result['settingValue']);
					}

					$settingsArray[$result['setting']] = $result['settingValue'];
				}
			}

			return $settingsArray;
		}

		public function setEmailSettingsWithPost() {
			if (isset($_POST['email_fromEmail']) && isset($_POST['email_fromName']) && isset($_POST['email_username']) && isset($_POST['email_password']) && isset($_POST['email_server']) && isset($_POST['email_port']) && isset($_POST['email_encryption'])) {
				if ($_POST['email_encryption'] == 'TLS' || $_POST['email_encryption'] == 'SSL' || $_POST['email_encryption'] == 'None') {
					if ($this->setEmailSettings(html_entity_decode($_POST['email_fromEmail']), html_entity_decode($_POST['email_fromName']), html_entity_decode($_POST['email_username']), $_POST['email_password'], html_entity_decode($_POST['email_server']), html_entity_decode($_POST['email_port']), html_entity_decode($_POST['email_encryption']))) {
						FlashMessage::flash('SystemSettingsMessage', 'The email settings were update successfully.');
					}
					else {
						FlashMessage::flash('SystemSettingsError', $this->emailSettingsErrorMsg);
						return false;
					}
				}
				else {
					FlashMessage::flash('SystemSettingsError', 'Invalid encryption setting of ' . sanitize($_POST['email_encryption']) . '.');
					return false;
				}
			}
			else {
				FlashMessage::flash('SystemSettingsError', 'All fields were not supplied in the Email Connection Settings form.');
				return false;
			}
		}

		public function setOtherSetting($setting, $settingValue) {
			if (isset($setting) && isset($settingValue)) {
				$stmt = $this->db_connection->prepare('SELECT null FROM othersettings WHERE setting = ?');
				if ($stmt->execute(array($setting))) {
					// If the setting is not in the database, then add it, if it is, then go to the elseif statement to update it
					if ($stmt->rowCount() == 0) {
						$stmt = null;
						$stmt = $this->db_connection->prepare('INSERT INTO othersettings (setting, settingValue) VALUES (?, ?)');
						if ($stmt->execute(array($setting, $settingValue))) {
							if (isset($_SESSION['user_name'])) {
								Logger::log('audit', 'Settings Change Success: The setting of ' . $setting . ' was set to ' . $settingValue . ' by "' . $_SESSION['user_name'] . '"');
							}
							else {
								Logger::log('audit', 'Settings Change Success: The setting of ' . $setting . ' was set to ' . $settingValue);
							}
							return true;
						}
					}
					elseif ($stmt->rowCount() >= 1) {
						$stmt = null;
						$stmt = $this->db_connection->prepare('UPDATE othersettings SET settingvalue = ? WHERE setting = ?');
						if ($stmt->execute(array($settingValue, $setting))) {
							if (isset($_SESSION['user_name'])) {
								Logger::log('audit', 'Settings Change Success: The setting of ' . $setting . ' was set to ' . $settingValue . ' by "' . $_SESSION['user_name'] . '"');
							}
							else {
								Logger::log('audit', 'Settings Change Success: The setting of ' . $setting . ' was set to ' . $settingValue);
							}
							return true;
						}
					}
				}

				Logger::log('error', 'The setting ' . $setting . ' was not set due to a database error.');
				throw new Exception('The setting was not set due to a database error');
			}

			throw new Exception('The setting and/or settingValue was not provided');
			return false;
		}

		public function getOtherSetting($setting) {
			if (isset($setting)) {
				$stmt = $this->db_connection->prepare('SELECT settingValue FROM othersettings WHERE setting = ?');
				if ($stmt->execute(array($setting)))
				if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
					return $result['settingValue'];
				}
			}
			
			return '';
		}

		public function setOtherSettingsWithPost() {
			if (isset($_POST['updateOtherSettings'])) {
				try {
					if (isset($_POST['email_emailTemplate'])) {
						$this->setOtherSetting('resetemailbody', html_entity_decode(trim($_POST['email_emailTemplate'])));
					}

					if (isset($_POST['options_EnableEmailReset'])) {
						$this->setOtherSetting('emailresetenabled', trim($_POST['options_EnableEmailReset']));
					}

					if (isset($_POST['options_EnableEmailReset'])) {
						$this->setOtherSetting('emailldapattribute', html_entity_decode(trim($_POST['email_ldapattribute'])));
					}

					if (isset($_POST['questions_failedattemptsallowed'])) {
						$this->setOtherSetting('failedattemptsallowed', trim($_POST['questions_failedattemptsallowed']));
					}

					FlashMessage::flash('SystemSettingsMessage', 'The settings were successfully set.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()));
				}
			}

			FlashMessage::flash('SystemSettingsError', 'The settings could not be set');
			return false;
		}

		private function addSecretQuestion($secretQuestion) {
			if (isset($secretQuestion)) {
				$stmt = $this->db_connection->prepare('SELECT null FROM secretquestions WHERE secretquestion = ?');
				if ($stmt->execute(array($secretQuestion))) {
					if ($stmt->rowCount() == 0) {
						$stmt = null;
						$stmt = $this->db_connection->prepare('INSERT INTO secretquestions (secretQuestion, enabled) VALUES (?, 1)');
						if ($stmt->execute(array($secretQuestion))) {
							if (isset($_SESSION['user_name'])) {
								Logger::log('audit', 'Add Secret Question Success: The secret question of "' . $secretQuestion . '" was added by "' . $_SESSION['user_name'] . '"');
							}
							else {
								Logger::log('audit', 'Add Secret Question Success: The secret question of "' . $secretQuestion . '" was added');
							}
							return true;
						}
					}
					else {
						throw new Exception('The secret question already exists');
					}
				}

				// If it didn't return true, then log and throw an exception
				Logger::log('error', 'The secret question could not be added due to a database error');
				throw new Exception('The secret question could not be added due to a database error');
			}

			throw new Exception('The secret question was not provided');
			return false;
		}

		public function getSecretQuestions() {
			if ($stmt = $this->db_connection->query('SELECT secretquestion, enabled FROM secretquestions')) {
				if ($secretQuestionsArray = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
					return $secretQuestionsArray;
				}
			}

			return false;
		}

		public function getNumOfSecretQuestions() {
			if ($stmt = $this->db_connection->query('SELECT secretquestion, enabled FROM secretquestions')) {
				return $stmt->rowCount();
			}

			return 0;
		}

		public function addSecretQuestionWithPost() {
			if (isset($_POST['secretquestion'])) {
				try {
					$this->addSecretQuestion(html_entity_decode(trim($_POST['secretquestion'])));
					FlashMessage::flash('SystemSettingsMessage', 'The secret question was successfully added.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()));
					return false;
				}
			}

			FlashMessage::flash('SystemSettingsError', 'The secret question couldn\'t be added');
			return false;
		}

		private function changeSecretQuestionStatus($secretQuestion) {
			if (isset($secretQuestion)) {
				$stmt = $this->db_connection->prepare('SELECT secretquestion, enabled FROM secretquestions WHERE secretquestion = ?');
				if ($stmt->execute(array($secretQuestion))) {
					if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$stmt = null;
						if ($result['enabled']) {
							$stmt = $this->db_connection->prepare('UPDATE secretquestions SET enabled = 0 WHERE secretquestion = ?');
							if ($stmt->execute(array($secretQuestion))) {
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Disable Secret Question Success: The secret question of "' . $secretQuestion . '" was disabled by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Disable Secret Question Success: The secret question of "' . $secretQuestion . '" was disabled');
								}
								
								return true;
							}
						}
						else {
							$stmt = $this->db_connection->prepare('UPDATE secretquestions SET enabled = 1 WHERE secretquestion = ?');
							if ($stmt->execute(array($secretQuestion))) {
								if (isset($_SESSION['user_name'])) {
									Logger::log('audit', 'Enable Secret Question Success: The secret question of "' . $secretQuestion . '" was enabled by "' . $_SESSION['user_name'] . '"');
								}
								else {
									Logger::log('audit', 'Enable Secret Question Success: The secret question of "' . $secretQuestion . '" was enabled');
								}
								return true;
							}
						}
					}
				}

				throw new Exception('The secret question status was not changed due to a database error');
			}

			throw new Exception('The secret question was not provided');
			return false;
		}

		public function changeSecretQuestionStatusFromPOST($secretQuestion) {
			if (isset($_POST['changeSecretQuestion']) && isset($_POST['secretQuestion'])) {
				try {
					$this->changeSecretQuestionStatus($_POST['secretQuestion']);
					FlashMessage::flash('SystemSettingsMessage', 'The secret question status was changed successfully.');
					return true;
				}
				catch (Exception $e) {
					FlashMessage::flash('SystemSettingsError', sanitize($e->getMessage()));
					return false;
				}
			}

			return false;
		}
	}
