<?php
    require_once(__DIR__ . '/../core/init.php');

    Class UserSettings {

        private $db_connection;

        public function __construct() {
            // Make sure the database can connect
            if (!$this->db_connection = startPDOConnection()) {
                echo '<h2 style="text-align:center">Database Connection Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
                die();
            }
        }

        private function setSecretQuestionToUser($username, $secretQuestion, $secretAnswer) {
            if (isset($username) && isset($secretQuestion) && isset($secretAnswer)) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting set a secret question for ' . $username);
                    throw new Exception('The Active Directory connection failed');
                }

                $resetPW = new ResetPW();
                
                if ($resetPW->isUserAllowedToReset($username, $AD)) {
                    if ($userGUID = $AD->getUserGUID($username)) {
                        $stmt = $this->db_connection->prepare('SELECT id, enabled FROM secretquestions WHERE secretquestion = ?');
                        if ($stmt->execute(array($secretQuestion))) {
                            if ($stmt->rowCount() == 1) {
                                if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $stmt = null;
                                    if (isset($result['id'])) {
                                        $stmt = $this->db_connection->prepare('SELECT null FROM usersecretquestions WHERE secretquestion_id = ? and userguid = ?');
                                        if ($stmt->execute(array($result['id'], $userGUID))) {
                                            // Hash the secret answer
                                            $secretAnswer = password_hash(strtolower($secretAnswer), PASSWORD_DEFAULT);
                                            // Check to see if the current secret question is set, if it isn't, make sure the secret question is enabled before adding it
                                            if ($stmt->rowCount() == 0 && $result['enabled']) {
                                                $stmt = null;
                                                $stmt = $this->db_connection->prepare('INSERT INTO usersecretquestions (userguid, secretquestion_id, secretanswer) VALUES(?, ?, ?)');
                                                if ($stmt->execute(array($userGUID, $result['id'], $secretAnswer))) {
                                                    return true;
                                                }
                                                else {
                                                    throw new Exception('The secret question was not set due to a database error');
                                                }
                                            }
                                            // If the secret question is already set, update it, even if it is disabled
                                            elseif ($stmt->rowCount() == 1) {
                                                $stmt = null;
                                                $stmt = $this->db_connection->prepare('UPDATE usersecretquestions SET secretanswer = ? WHERE secretquestion_id = ?');
                                                if ($stmt->execute(array($secretAnswer, $result['id']))) {
                                                    return true;
                                                }
                                                else {
                                                    throw new Exception('The secret question was not set due to a database error');
                                                }
                                            }
                                            // If the user isn't currently using this secret question and it is disabled, then throw an exception
                                            elseif (!$result['enabled']) {
                                                throw new Exception('The secret question was not set because the secret question is disabled');
                                            }
                                            else {
                                                throw new Exception('The secret question was not set due to a database error');
                                            }
                                        }
                                    }
                                }
                            }
                            elseif ($stmt->rowCount() > 1) {
                                Logger::log ('error', 'The secret question of "' . $secretQuestion . '"" was found multiple times in the database');
                                throw new Exception('The secret question was found multiple times in the database');
                            }
                            else {
                                Logger::log ('error', 'The secret question of "' . $secretQuestion . '"" was not found in the database');
                                throw new Exception('The secret question was not set due to a database error');
                            }
                        }
                        else {
                            Logger::log ('error', 'The secret question was not set due to a database error');
                            throw new Exception('The secret question was not set due to a database error');
                        }
                    }
                    else {
                        Logger::log ('error', 'The GUID for user "' . $username . '"" was not found');
                        throw new Exception('The secret question was not set due to an Active Directory error');
                    }

                }
                else {
                    throw new Exception('You do not have permission to use ADReset. Please contact the Help Desk for assistance.');
                }
            }

            throw new Exception('Not all of the required values were provided.');
        }

        public function numSecretQuestionsSetToUser($username) {
            if (isset($username)) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting set a secret question for ' . $username);
                    throw new Exception('The Active Directory connection failed');
                }

                if ($userGUID = $AD->getUserGUID($username)) {
                    $stmt = $this->db_connection->prepare('SELECT null FROM usersecretquestions WHERE userguid = ?');
                    if ($stmt->execute(array($userGUID))) {
                        return $stmt->rowCount();
                    }
                }
                else {
                    Logger::log ('error', 'The GUID for user "' . $username . '"" was not found');
                    throw new Exception('The secret questions could not be queried due to an Active Directory error');
                }
            }

            throw new Exception('The username was not provided.');
        }

        public function getSecretQuestionsSetToUser($username) {
            if (isset($username)) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting set a secret question for ' . $username);
                    return array();
                }

                if ($userGUID = $AD->getUserGUID($username)) {
                    $stmt = $this->db_connection->prepare('SELECT secretquestion_id FROM usersecretquestions WHERE userguid = ?');
                    if ($stmt->execute(array($userGUID))) {
                        if ($questionIDs = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                            $questionArray = array();
                            $stmt = null;
                            $stmt = $this->db_connection->prepare('SELECT secretquestion FROM secretquestions WHERE id = ?');
                            foreach ($questionIDs as $questionID) {
                                if ($stmt->execute(array($questionID['secretquestion_id']))) {
                                    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        if (isset($result['secretquestion'])) {
                                            $questionArray[] = $result['secretquestion'];
                                        }
                                        else {
                                            Logger::log ('error', 'The secret questions for user "' . $username . '" could not be retrieved due to a database error');
                                            return array();
                                        }
                                    }
                                    else {
                                        Logger::log ('error', 'The secret questions for user "' . $username . '" could not be retrieved due to a database error');
                                        return array();
                                    }
                                }
                                else {
                                    Logger::log ('error', 'The secret questions for user "' . $username . '" could not be retrieved due to a database error');
                                    return array();
                                }
                            }

                            return $questionArray;
                        }
                        else {
                            // This is most likely due to there not being any results
                            return array();
                        }
                    }
                }
                else {
                    Logger::log ('error', 'The GUID for user "' . $username . '" could not be retrieved');
                    return array();
                }

                Logger::log ('error', 'The secret questions for user "' . $username . '" could not be retrieved due to a database error');
            }

            return array();
        }

        // This returns all available secret questions
        public function getSecretQuestions() {
            if ($stmt = $this->db_connection->query('SELECT secretquestion, enabled FROM secretquestions')) {
                if ($secretQuestionsArray = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                    return $secretQuestionsArray;
                }
            }

            return array();
        }

        public function getUniqueSecretQuestionsForUser($username) {
            if (isset($username)) {
                if ($secretQuestions = $this->getSecretQuestions()) {
                    if ($userSecretQuestions = $this->getSecretQuestionsSetToUser($username)) {
                        $uniqueSecretQuestions = array();
                        foreach ($secretQuestions as $secretQuestion) {
                            $match = false;
                            if ($secretQuestion['enabled']) {
                                foreach ($userSecretQuestions as $userSecretQuestion) {
                                    if ($userSecretQuestion == $secretQuestion['secretquestion']) {
                                        $match = true;
                                    }
                                }

                                if (!$match) {
                                    $uniqueSecretQuestions[] = $secretQuestion['secretquestion'];
                                }
                            }
                        }

                        return $uniqueSecretQuestions;
                    }
                    else {
                        foreach ($secretQuestions as $secretQuestion) {
                            if ($secretQuestion['enabled']) {
                                $uniqueSecretQuestions[] = $secretQuestion['secretquestion'];
                            }
                        }

                        return $uniqueSecretQuestions;
                    }
                }
            }
            

            return array();
        }

        private function setErrorAndQuit($message) {
            if (isset($message)) {
                FlashMessage::flash('ChangeUserSettingsError', $message);
                header('Location: /settings/usersettings.php'); 
                exit();
            }
        }


        public function addSecretQuestionWithPost() {
            if (isset($_POST['addSecretAnswer']) && isset($_POST['secretAnswer']) && !empty($_POST['secretAnswer']) && isset($_POST['secretQuestion']) && !empty($_POST['secretQuestion']) ) {
                if ($this->numSecretQuestionsSetToUser($_SESSION['user_name']) < 3) {
                    try {
                        $this->setSecretQuestionToUser($_SESSION['user_name'], $_POST['secretQuestion'], $_POST['secretAnswer']);
                        if ($numSecretQuestionsSet = $this->numSecretQuestionsSetToUser($_SESSION['user_name']) == 3) {
                            FlashMessage::flash('ChangeUserSettingsMessage', 'You have successfully set all of your required secret questions. You may now log out.');
                        }
                        else {
                            FlashMessage::flash('ChangeUserSettingsMessage', 'The new secret question was successfully set.');  
                        }

                        header('Location: /settings/usersettings.php'); 
                        exit();
                    }
                    catch (Exception $e) {
                        $this->setErrorAndQuit(sanitize($e->getMessage()));
                    }
                    
                }
                else {
                    $this->setErrorAndQuit('The secret question was not set. You can only set a maximum of three secret questions.');
                }
            }
            else {
                $this->setErrorAndQuit('The secret question was not set. You must fill in all the required fields to add a new secret question.');
            }

            $this->setErrorAndQuit('The secret question was not set.');
            return false;
        }

        public function editSecretQuestionWithPost() {
            if (isset($_POST['editSecretAnswer']) && isset($_POST['secretAnswer']) && isset($_POST['secretQuestion'])) {

                if ($secretQuestions = $this->getSecretQuestionsSetToUser($_SESSION['user_name'])) {
                    foreach($secretQuestions as $secretQuestion) {
                        if ($secretQuestion == $_POST['secretQuestion']) {
                            $this->setSecretQuestionToUser($_SESSION['user_name'], $_POST['secretQuestion'], $_POST['secretAnswer']);
                            FlashMessage::flash('ChangeUserSettingsMessage', 'The secret question was successfully set.');
                            header('Location: /settings/usersettings.php'); 
                            exit();
                            return true;
                        }
                    }
                }

                $this->setErrorAndQuit('The secret question was not set because it is invalid.');
                
            }
            else {
                $this->setErrorAndQuit('The secret question was not set. You must fill in all the required fields to add a new secret question.');
            }

            $this->setErrorAndQuit('The secret question was not set.');
            return false;
        }

        public function resetSecretQuestionsForUser($username) {
            if (isset($username)) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting reset secret questions for ' . $username);
                    $this->setErrorAndQuit('The secret questions were not reset because the connection to Active Directory failed.');
                }

                if ($userGUID = $AD->getUserGUID($username)) {
                    $stmt = $this->db_connection->prepare('DELETE FROM usersecretquestions WHERE userguid = ?');
                    if ($stmt->execute(array($userGUID))) {
                        FlashMessage::flash('ChangeUserSettingsMessage', 'Your secret questions were successfully reset.');
                        header('Location: /settings/usersettings.php'); 
                        exit();
                        return true;
                    }
                    else {
                        $this->setErrorAndQuit('The secret questions were not reset due to a database error.');
                    }
                }
                else {
                    Logger::log('error', 'The user\'s GUID was not found ' . $username);
                }
            }

            return false;
        }
    }
