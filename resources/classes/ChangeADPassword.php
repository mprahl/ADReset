<?php
    require_once(__DIR__ . '/../core/init.php');
    require_once(RESOURCE_DIR . 'functions/ADPasswordPolicyMatch.php');

    Class ChangeADPassword {
        private $AD_connection;

        public function __construct() {
            try {
                $this->AD_connection = new AD();
            }
            catch (Exception $e) {
                $this->setErrorAndQuit('The Domain Controller could not be contacted.');
            }
        }

        private function setErrorAndQuit($message) {
            if (isset($message)) {
                FlashMessage::flash('ChangePWError', $message);
                header('Location: /changepw.php'); 
                exit();
            }

            return false;
        }

        public function changeFromPOST() {
            if (isset($_POST['user_name'], $_POST['user_password'], $_POST['user_new_password'], $_POST['user_confirm_password'], $_POST['user_captcha'])) {
                if (trim($_POST['user_captcha']) == $_SESSION['changepw_captcha']) {
                    if (trim($_POST['user_new_password']) == trim($_POST['user_confirm_password'])) {
                        if (ADPasswordPolicyMatch(trim($_POST['user_new_password']))) {
                            if ($this->AD_connection->changePassword(trim($_POST['user_name']), trim($_POST['user_password']), trim($_POST['user_new_password']))) {
                                Logger::log('audit', 'Password Change Success: The user "' . $_POST['user_name'] . '" changed their password.');
                                FlashMessage::flash('ChangePWMessage', 'Your password has been changed successfully.');
                                header('Location: /changepw.php'); 
                                exit();
                            }
                            else {
                                Logger::log('audit', 'Password Change Failure: The user "' . $_POST['user_name'] . '" failed at changing their password.');
                                $this->setErrorAndQuit('Your password could not be changed due to an incorrect password. If this is an error, please contact the Help Desk.');
                            }
                        }
                        else {
                            $this->setErrorAndQuit('Your password could not be changed because it did not meet the complexity requirements. ' . ADPasswordPolicyWritten());
                        }
                    }
                    else {
                        $this->setErrorAndQuit('Your password could not be changed because the two entries of your new password did not match.');
                    }
                }
                else {
                    $this->setErrorAndQuit('Your password could not be changed because the verification code did not match. Please try again.');
                }
            }

            return false;
        }
    }
