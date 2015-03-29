<?php

require_once(__DIR__ . '/../core/init.php');

// This login class was heavily inspired by this source code https://github.com/panique/php-login-minimal
class ADLogin {
    private $ad_connection;
    private $connectionSettings;

    public function __construct() {
        // Get the connection settings to connect to AD. If it fails notify the user.
        if (!$this->getConnectionSettings()) {
            echo '<h2 style="text-align:center">Active Directory Connection Settings Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
            Logger::log ('error', 'The database could not retrieve the connection settings for Active Directory');
            exit();
        }

        // Create or read the session
        @session_start();

        // If the user is currently logged in as local, log them out
        if (isset($_SESSION['auth_source'])) {
            if ($_SESSION['auth_source'] == 'local') {
                $this->logout();
            }
        }

        // Check the to see if the user is trying to log in or off
        if (isset($_GET["logout"])) {
            $this->logout();
        }
        // If the login form was submitted, then call the doLoginWithPostData function
        elseif (isset($_POST["login"])) {
            $this->loginWithPOST();
        }
    }

    public function __destruct() {
        //Close the AD connection on destruction
        @ldap_unbind($this->ad_connection);
    }

    private function getConnectionSettings() {
        $connectionSettingsObject = new ConnectionSettings();
        // Just get the settings we need to login as a user supplied by POST
        $this->connectionSettings['DC'] = $connectionSettingsObject->get('DC');
        $this->connectionSettings['port'] = $connectionSettingsObject->get('port');
        $this->connectionSettings['domainName'] = $connectionSettingsObject->get('domainName');
        // Make sure all the settings are set, otherwise we don't want anyone to be able to login
        if (!empty($this->connectionSettings) && $connectionSettingsObject->areAllSettingsSet()) {
            return true;
        }
        // default return
        return false;
    }

    private function connect() {
        if (isset($this->connectionSettings['DC']) && isset($this->connectionSettings['port'])) {
            if ($this->ad_connection = ldap_connect($this->connectionSettings['DC'], $this->connectionSettings['port'])) {
                return true;
            }
        }

        return false;
    }

    private function bind($username, $password) {
        if (isset($username) && isset($password)) {
            ldap_set_option($this->ad_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ad_connection, LDAP_OPT_REFERRALS, 0);

            $ldapUsername = $username;
            // If they entered username@domain.local, remove the @domain.local
            $ldapUsername = preg_replace('{@.*$}', '' , $ldapUsername);
            // If they entered domain\username, then remove domain\
            $ldapUsername = preg_replace('{^.*\\\}', '' , $ldapUsername);
            // Add the @domain.local
            $ldapUsername = $ldapUsername . '@' . $this->connectionSettings['domainName'];
            
            if (@ldap_bind( $this->ad_connection, $ldapUsername, $password )) {
                return true;
            }
            else {
                return false;
            }
        }

        else {
            return false;
        }
    }


    public function isUserAdmin($username) {
        if (isset($username)) {
            if ($systemSettings = new SystemSettings()) {
                if ($adminGroups = $systemSettings->getAdminGroups()) {
                    try {
                        $AD = new AD();
                        $userGroups = $AD->getMembership($username);
                        foreach ($userGroups as $userGroup) {
                            foreach ($adminGroups as $adminGroup) {
                                if ($userGroup == $adminGroup['samaccountname']) {
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
        }

        return false;        
    }

    private function setLoginErrorAndQuit($message) {
        if (isset($message)) {
            FlashMessage::flash('LoginError', $message);
            if (isset($_GET['page'])){
                header('Location: /account.php?page=' . $_GET['page']);
            }
            else {
               header('Location: /account.php'); 
            }
            exit();
        }
    }

    private function loginWithPOST() {
        // Verfiy the contents that were submitted by the form
        if (empty($_POST['user_name'])) {
            FlashMessage::flash('LoginError', 'The Username field was empty.');
            header('Location: /account.php');
            exit();
        }
        elseif (empty($_POST['user_password'])) {
            FlashMessage::flash('LoginError', 'The Password field was empty.');
            header('Location: /account.php');
            exit();
        }
        elseif (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
            // Trimming the whitespace.
            $user_name = trim($_POST['user_name']);
            $user_password = trim($_POST['user_password']);
            // Start the AD connection
            if ($this->connect()) {
                if ($this->bind($user_name, $user_password)) {
                    // Create a ResetPW object to gain access to the isUserAllowedToReset function.
                    $resetPW = new ResetPW();
                    $AD = new AD();

                    // Write the user's data into a PHP SESSION
                    // Determine the user's privilege level
                    if ($this->isUserAdmin($user_name)) {
                        $_SESSION['privilege'] = 'admin';
                    }
                    elseif ($resetPW->isUserAllowedToReset($user_name, $AD)) {
                        $_SESSION['privilege'] = 'user';
                    }
                    else {
                        $this->setLoginErrorAndQuit('You do not have permission to use ADReset. Please contact the Help Desk for assistance.');
                    }

                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['auth_source'] = 'domain';
                    $_SESSION['user_login_status'] = 1;

                    Logger::log ('audit', 'Login Success: The user "' . $user_name .  '"" logged in');
                    
                    // If the page GET variable was set, then redirect the user to that page after logging in
                    if (isset($_GET['page'])){
                        header('Location: /' . urldecode($_GET['page']));
                    }
                    else {
                       header('Location: /account.php'); 
                    }
                    exit();
                }
                // If the username or password is incorrect, notify the user trying to log in
                else {
                    // If the DC is not available, let the user know so they can contact Help Desk
                    if (ldap_error($this->ad_connection) == 'Can\'t contact LDAP server') {
                        Logger::log ('error', 'The Domain Controller could not be contacted');
                        $this->setLoginErrorAndQuit('The Domain Controller could not be contacted.<br />Please try again.');
                    }
                    elseif (ldap_error($this->ad_connection) == 'Invalid credentials') {
                        Logger::log ('audit', 'Login Failure: The username or password is incorrect for the user "' . $user_name . '"');
                        $this->setLoginErrorAndQuit('The Username or Password is incorrect. Please try again.');
                    }
                    else {
                        Logger::log ('error', 'The following error occured when "' . $user_name . '"" attempted to login: ' . ldap_error($this->ad_connection));
                        $this->setLoginErrorAndQuit('The Username or Password is incorrect. Please try again.');
                    }
                }
            }
            // If the AD connection fails, notify the user trying to log in
            else {
                Logger::log ('error', 'The Domain Controller could not be contacted');
                $this->setLoginErrorAndQuit('There was a problem connecting to the Domain Controller. Please try again.');
            }
        }
    }

    public function logout() {
        if (isset($_SESSION['user_name'])) {
            Logger::log ('audit', 'Logout Success: The user "' . $_SESSION['user_name'] .  '"" logged out');
        }
        
        // If the page GET request was set, then redirect them to that page after log out. If not, redirect them to the homepage.
        if (isset($_GET['page'])) {
            
            $page = $_GET['page'];

            // Delete the user's session
            $_SESSION = array();
            session_destroy();
            header('Location: /' . $page);
        }
        else {
            // Delete the user's session
            $_SESSION = array();
            session_destroy();
            header('Location: /index.php');
        }
        exit();
    }
    
    public function isUserLoggedIn() {
        if (isset($_SESSION['user_login_status']) AND $_SESSION['user_login_status'] == 1) {
            return true;
        }
        // default return
        return false;
    }
}
