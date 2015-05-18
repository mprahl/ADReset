<?php
    require_once(__DIR__ . '/../core/init.php');
    require_once(RESOURCE_DIR . 'functions/ADPasswordPolicyMatch.php');
    Class ResetPW {

        private $db_connection;

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

        // Provide a username and an AD connection object
        public function isUserAllowedToReset($username, AD $AD) {
            if (isset($username) && isset($AD)) {
                if ($systemSettings = new SystemSettings()) {
                    if ($resetGroups = $systemSettings->getResetGroups()) {
                        try {
                            $userGroups = $AD->getMembership($username);

                            if ($adminGroups = $systemSettings->getAdminGroups()) {
                                // If the user is an Admin, don't let them reset their password
                                foreach ($userGroups as $userGroup) {
                                    foreach ($adminGroups as $adminGroup) {
                                        if ($userGroup == $adminGroup['samaccountname']) {
                                            return false;
                                        }
                                    }
                                }
                            }

                            // Check to see if they are in the reset group
                            foreach ($userGroups as $userGroup) {
                                foreach ($resetGroups as $resetGroup) {
                                    if ($userGroup == $resetGroup['samaccountname']) {
                                        return true;
                                    }
                                }
                            }
                        }
                        catch(Exception $e) {
                            Logger::log('error', $e . ' when attempting to check the user membership of ' . $username);
                            return false;
                        }
                    }
                }
                Logger::log('error', 'ADReset failed when attempting to check the user membership of ' . $username);
            }

            return false;  
        }

        public function generateAndSendCode($username) {
            // Generate a random number between 99999 and 9999999999999. With that, then MD5 it and then make the string 15-20 characters in length
            $generated_code = substr(md5(rand(99999, 9999999999999)), 0, rand(15,20));

            if ($generated_code) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting to generate a recovery code for ' . $username);
                    throw new Exception('The Active Directory connection failed');
                }
                
                if ($this->isUserAllowedToReset($username, $AD)) {
                    if ($userGUID = $AD->getUserGUID($username)) {
                        $stmt = $this->db_connection->prepare('INSERT INTO emailreset (userguid, code, createtime) VALUES (?, ?, ?)');

                        if ($stmt->execute(array($userGUID, $generated_code, date ("Y-m-d H:i:s")))) {
                            $stmt = null;
                            $systemSettings = new SystemSettings();

                            $emailAttribute = $systemSettings->getOtherSetting('emailldapattribute');

                            if (isset($emailAttribute) && !empty($emailAttribute)) {
                                $email = $AD->getUserAttribute($username, $emailAttribute);
                            }
                            else {
                                $email = $AD->getEmail($username);
                            }

                            if ($email) {
                                // Based on if it's HTTP or HTTPS, determine the url to send in the email
                                if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
                                    $url = 'https://' . $_SERVER['HTTP_HOST'] . '/newpw.php?id=' . $generated_code;
                                }
                                else {
                                    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/newpw.php?id=' . $generated_code;
                                }

                                if ($body = $systemSettings->getOtherSetting('resetemailbody')) {
                                    // Replace the shortcodes [reset-link] and [user-name] with the appropriate values
                                    $body = str_replace('[reset-link]', '<a href="' . $url . '">' . $url . '</a>', $body);
                                    $body = str_replace('[user-name]', $username, $body);

                                    if (!sendEmail($email, 'Password Reset Request', $body)) {
                                        Logger::log('error', 'The password reset email could not be sent out for the user "' . $username . '". Make sure your email settings are properly set.');
                                        throw new Exception('The email failed to send');
                                    }

                                    Logger::log ('audit', 'Reset Initiated: A password reset email request was initiated for the user "' . $username . '"');
                                    return true;
                                }                               
                            }
                            else {
                                Logger::log('error', 'The user "' . $username . '" failed to a initiate a password request via email because their email was not set in Active Directory.');
                                throw new Exception('The reset email is not properly set in Active Directory.');
                            }
                        }
                        else {
                            Logger::log('error', 'The database failed to insert the generated code for "' . $username . '"');
                            throw new Exception('The database connection failed');
                        }
                    }
                }
                else {
                    throw new Exception('The user does not have permission to use ADReset');
                }
                
            }

            throw new Exception('Unknown error');
            return false;
        }

        public function isCodeValid($id) {
            if (isset($id)) {
                $stmt = $this->db_connection->prepare('SELECT * FROM emailreset WHERE code = ?');
                $stmt->execute(array(trim($id)));

                if ($stmt->rowCount() == 1) {
                    $codeProperties = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($codeProperties) && isset($codeProperties['createtime'])) {
                        // Check to see if the code creation date is less than 24 hours old
                        if ((time() - (60*60*24)) < strtotime($codeProperties['createtime'])) {
                            $stmt = null;
                            return true;
                        }
                    }
                }
            }

            Logger::log ('audit', 'Reset Failure: A password reset via an email failed with an invalid token');
            $stmt = null;
            return false;
        }

        public function generateQuestionsCode($username) {
            // Generate a random number between 99999 and 9999999999999. With that, then MD5 it and then make the string 15-20 characters in length
            $generated_code = substr(md5(rand(99999, 9999999999999)), 0, rand(15,20));

            if ($generated_code) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting to generate a recovery code for ' . $username);
                    throw new Exception('The Active Directory connection failed');
                }

                if ($this->isUserAllowedToReset($username, $AD)) {
                    if ($userGUID = $AD->getUserGUID($username)) {
                        $stmt = $this->db_connection->prepare('INSERT INTO emailreset (userguid, code, createtime) VALUES (?, ?, ?)');

                        if ($stmt->execute(array($userGUID, $generated_code, date ("Y-m-d H:i:s")))) {
                            $stmt = null;
                            Logger::log ('audit', 'Reset Initiated: A password reset with secret questions was initiated for the user "' . $username . '"');
                            return $generated_code; 
                        }
                        else {
                            Logger::log('error', 'The database failed to insert the generated code for "' . $username . '"');
                            throw new Exception('The database connection failed');
                        }
                    }
                }
                else {
                    throw new Exception('The user does not have permission to use ADReset');
                }
                
            }

            return false;
        }

        // The same as $this->isCodeValid except the expiration is 15 minutes
        public function isQuestionsCodeValid($id) {
            if (isset($id)) {
                $stmt = $this->db_connection->prepare('SELECT * FROM emailreset WHERE code = ?');
                $stmt->execute(array(trim($id)));

                if ($stmt->rowCount() == 1) {
                    $codeProperties = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($codeProperties) && isset($codeProperties['createtime'])) {
                        // Check to see if the code creation date is less than 15 minutes old
                        if ((time() - (15*60)) < strtotime($codeProperties['createtime'])) {
                            $stmt = null;
                            return true;
                        }
                    }
                }
            }

            Logger::log ('audit', 'Reset Failure: A password reset via an email failed with an invalid token');
            $stmt = null;
            return false;
        }

        public function isCodeValidFromEmail() {
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
            }
            elseif (isset($_POST['id'])) {
                $id = $_POST['id'];
            }

            if (isset($id)) {
                if ($this->isCodeValid($id)) {
                    return true;
                }
                else {
                    // If the code is invalid then notify the user with a modal.
                    FlashMessage::flash('InvalidCodeError', '/js/invalidCodePrompt.js');
                }
            }

            return false;
        }
    
        private function cleanUpUserCodes($GUID) {
            if (isset($GUID)) {
                $stmt = $this->db_connection->prepare('DELETE FROM emailreset WHERE userguid = ?');
                if ($stmt->execute(array($GUID))) {
                    return true;
                }
                else {
                    //log here
                    return false;
                }
            }
        }

        public function setNewPassword() {
            if (isset($_POST['user_password_new']) && isset($_POST['user_password_repeat'])) {
                if ($_POST['user_password_new'] === $_POST['user_password_repeat']) {
                    if (ADPasswordPolicyMatch($_POST['user_password_new'])) {
                        $stmt = $this->db_connection->prepare('SELECT userguid FROM emailreset WHERE code = ?');

                        if ($stmt->execute(array(trim($_POST['id'])))) {
                            if ($stmt->rowCount() == 1) {
                                $GUID = $stmt->fetch(PDO::FETCH_ASSOC);
                                $GUID = $GUID['userguid'];
                                if (isset($GUID)) {
                                    try {
                                        $AD = new AD();
                                    }
                                    catch(Exception $e) {
                                        Logger::log('error', $e . ' when attempting to change the password for ' . $username);
                                        return false;
                                    }
                                    $username = $AD->getUserFromGUID($GUID)['samaccountname'];
                                    if (isset($username)) {
                                        if ($AD->unlockAccount($username) && $AD->setPassword($username, $_POST['user_password_new'])) {
                                            Logger::log ('audit', 'Reset Success: A password was reset for the user "' . $username . '"');
                                            // Then clean up the reset password codes from the database since the password reset was succesfull
                                            $this->cleanUpUserCodes($GUID);
                                            return true;
                                        }

                                        else {
                                            FlashMessage::flash('NewPWError', 'The password could not be reset. Please make sure the password meets the password policy standards.');
                                        }
                                    }

                                    else {
                                        Logger::log ('error', 'The username associated with this password reset could not be found for the user "' . $_POST['user_name'] . '"');
                                        FlashMessage::flash('NewPWError', 'The username associated with this password reset could not be found. Please try again.');
                                    }
                                }

                                else {
                                    Logger::log ('error', 'The database couldn\'t find the GUID associated with the email reset code');
                                    FlashMessage::flash('NewPWError', 'The database couldn\'t find the required data. Please try again.');
                                }
                            }

                            else {
                                Logger::log ('error', 'The database couldn\'t find the email reset code specified');
                                FlashMessage::flash('NewPWError', 'The database couldn\'t find the required data. Please try again.');
                            }
                        }

                        else {
                            Logger::log ('error', 'The database couldn\'t execute the query to find the userguid associated with the email reset code');
                            FlashMessage::flash('NewPWError', 'The database couldn\'t find the required data. Please try again.');
                        }
                    }

                    else {
                        FlashMessage::flash('NewPWError', 'The password does not match the password policy<br />' . ADPasswordPolicyWritten());
                    }
                }

                else {
                    FlashMessage::flash('NewPWError', 'The passwords entered do not match. Please try again.');
                }
            }

            else {
                FlashMessage::flash('NewPWError', 'The required form fields were not properly entered. Please try again.');
            }

            return false;
        }

        public function verifySecretQuestionSetToUser($username, $secretQuestion, $secretAnswer) {
            if (isset($username) && isset($secretQuestion) && isset($secretAnswer)) {
                try {
                    $AD = new AD();
                }
                catch(Exception $e) {
                    Logger::log('error', $e . ' when attempting to check secret questions for ' . $username);
                    throw new Exception('The Active Directory connection failed');
                }

                if ($this->isUserAllowedToReset($username, $AD)) {
                    if ($userGUID = $AD->getUserGUID($username)) {
                        $stmt = $this->db_connection->prepare('SELECT id FROM secretquestions WHERE secretquestion = ?');
                        if ($stmt->execute(array($secretQuestion))) {
                            if ($stmt->rowCount() == 1) {
                                if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $stmt = null;
                                    $stmt = $this->db_connection->prepare('SELECT secretanswer FROM usersecretquestions WHERE userguid = ? AND secretquestion_id = ?');
                                    if ($stmt->execute(array($userGUID, $result['id']))) {
                                        if ($secretAnswerResult = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $stmt = null;
                                            if (password_verify(strtolower($secretAnswer), $secretAnswerResult['secretanswer'])) {
                                                return true;
                                            }
                                            else {
                                                Logger::log ('audit', 'Secret Question Verification Failure: The secret answer provided was incorrect by "' . $username . '"');
                                            }
                                        }
                                    }
                                }
                            }
                            else {
                                Logger::log ('error', 'The secret question of "' . $secretQuestion . '"" was found multiple times in the database');
                            }
                        }
                    }
                }
            }

            return false;
        }

        public function setFailedAttempt($username) {
            if (isset($username)) {
                try {
                    $AD = new AD();
                }
                catch (Exception $e) {
                    return false;
                }

                if ($userGUID = $AD->getUserGUID($username)) {
                    $stmt = $this->db_connection->prepare('INSERT INTO sqfailedattempts (userguid, failuretime) VALUES (?, ?)');
                    if ($stmt->execute(array($userGUID, date ("Y-m-d H:i:s")))) {
                        return true;
                    }
                    else {
                        Logger::log ('error', 'The user "' . $username . '" failed to answer their secret questions properly but it could not be recorded due to a database error.');
                    }
                }
                else {
                    Logger::log ('error', 'The user "' . $username . '" failed to answer their secret questions properly but it could not be recorded due to their GUID not being found.');
                }
            }

            return false;
        }

        public function getNumberOfFailedAttempts($username) {
            if (isset($username)) {
                try {
                    $AD = new AD();
                }
                catch (Exception $e) {
                    return false;
                }

                if ($userGUID = $AD->getUserGUID($username)) {
                    $stmt = $this->db_connection->prepare('SELECT failuretime FROM sqfailedattempts WHERE userguid = ?');
                    if ($stmt->execute(array($userGUID))) {
                        $failedAttemptTimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (isset($failedAttemptTimes)) {
                            $numFailedAttempts = 0;
                            foreach ($failedAttemptTimes as $failedAttemptTime) {
                                // Check to see if the failure attempt is less than 15 minutes old
                                if ((time() - (15*60)) < strtotime($failedAttemptTime['failuretime'])) {
                                    $numFailedAttempts++;
                                }
                            }

                            return $numFailedAttempts;
                        }
                    }
                }
            }

            return false;
        }
    }
