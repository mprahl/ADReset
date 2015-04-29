<?php
    require_once(__DIR__ . '/../core/init.php');

    Class ConnectionSettings {
        private $db_connection;

        public function __construct() {
            if (!$this->db_connection = startPDOConnection()) {
                echo '<h2 style="text-align:center">Database Connection Error:</h2><h3 style="text-align:center">Please contact the Help Desk with this error.</h3>';
                die();
            }
        }

        public function __destruct() {
            @$this->db_connection = null;
        }

        public function get($setting) {
            if ($this->db_connection && isset($setting)) {

                // If the application is requesting the baseDN, then format it based on the domain
                if ($setting == 'baseDN') {
                    return $this->getBaseDN();
                }

                $stmt = $this->db_connection->prepare('SELECT settingValue FROM adconnectionsettings WHERE setting = ?');
                $stmt->execute(array($setting));

                if($stmt->rowCount() == 1) {
                    $returnedSetting = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($returnedSetting)) {
                        // If the setting being returned is a password, then decrypt it.
                        if ($setting =='password') {
                            $returnedSetting['settingValue'] = Crypto::cust_decrypt($returnedSetting['settingValue']);
                        }

                        $stmt = null;
                        return $returnedSetting['settingValue'];
                    }
                }
                else {
                    $stmt = null;
                }
            }

            return '';
        }

        public function getAll() {
            if ($this->db_connection) {

                $result = $this->db_connection->query('SELECT setting, settingValue FROM adconnectionsettings');
                
                $settingsArray = array();
                if ($result->rowCount() != 0) {
                    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row => $settingsGroup) {
                        // If it's the password in the foreach loop than decrypt it
                        if ($settingsGroup['setting'] =='password') {
                            $settingsGroup['settingValue'] = Crypto::cust_decrypt($settingsGroup['settingValue']);
                        }

                        $settingsArray[$settingsGroup['setting']] = $settingsGroup['settingValue'];
                    }

                    $result = null;

                    if (isset($settingsArray['domainName'])) {
                        $settingsArray['baseDN'] = $this->getBaseDN($settingsArray['domainName']);
                    }

                    return $settingsArray;
                }
            }
            // default return
            return array();
        }

        public function set($setting, $settingValue) {
            if ($this->db_connection) {
                if (isset($setting) && isset($settingValue)) {

                    // If the setting being set is a password, then encrypt it.
                    if ($setting == 'password') {
                        $settingValue = Crypto::cust_encrypt($settingValue);
                    }
                    // If the setting being set is the DC, then add on the ldaps:// if it wasn't previously added
                    elseif ($setting == 'DC' && substr($settingValue, 0, 8) != 'ldaps://') {
                        $settingValue = 'ldaps://' . $settingValue;
                    }

                    // If the value is set in the database, then update it
                    if (!empty($this->get($setting))) {
                        $stmt = $this->db_connection->prepare('UPDATE adconnectionsettings SET settingValue = ? WHERE setting = ?');
                        if ($stmt->execute(array($settingValue, $setting))) {
                            $stmt = null;
                            return true;
                        }
                    }
                    // If the setting is not set in the database then add it
                    else {
                        $stmt = $this->db_connection->prepare('INSERT INTO adconnectionsettings (setting, settingValue) VALUES (?, ?)');
                        if ($stmt->execute(array($setting, $settingValue))) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        public function getBaseDN($domain = '') {
            if (empty($domain)) {
                $domain = $this->get('domainName');
            }

            if (!empty($domain)) {
                // Breakup the DNS Domain Name
                $expandedDomain = explode('.', $domain);
                $baseDN = '';
                if (count($expandedDomain) > 0) {
                    for ($i = 0; $i < count($expandedDomain); $i++) {
                        if ($i == 0) {
                            $baseDN = $baseDN . 'DC=' . $expandedDomain[$i];
                        }
                        // Ensure there are commas seperating each part of the path
                        else {
                            $baseDN = $baseDN . ',DC=' . $expandedDomain[$i];
                        }   
                    }
                }

                return $baseDN;
            }

            // default return
            return '';
        }

        public function areAllSettingsSet() {
            $connectionSettings = $this->getAll();
            if (!empty($connectionSettings)) {
                // Make sure that the 
                if (isset($connectionSettings['DC']) && isset($connectionSettings['port']) && isset($connectionSettings['username']) && isset($connectionSettings['password']) && isset($connectionSettings['domainName']) && isset($connectionSettings['baseDN'])) {
                    return true;
                }
            }
            // default return
            return false;
        }

        public function setWithPost() {

            if ($this->db_connection) {
                // Check to see if all the required connection data was set
                if (isset($_POST['connection_dc'], $_POST['connection_port'], $_POST['connection_username'], $_POST['connection_domainName'], $_POST['connection_password'])) {
                    $DC = 'ldaps://' . trim($_POST['connection_dc']);
                    $port = trim($_POST['connection_port']);
                    $username = trim($_POST['connection_username']);
                    $password =  trim($_POST['connection_password']);
                    $domainName = trim($_POST['connection_domainName']);

                    if ($this->testSettings($DC, $port, $username, $password, $domainName)) {
                        if (!$this->set('DC', $DC)) {
                            FlashMessage::flash('ChangeConnectionSettingsError', 'The Domain Controller couldn\'t be set in the database.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }

                        elseif (!$this->set('port', $port)) {
                            FlashMessage::flash('ChangeConnectionSettingsError', 'The LDAPS port couldn\'t be set in the database.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }

                        elseif (!$this->set('username', $username)) {
                            FlashMessage::flash('ChangeConnectionSettingsError', 'The Username couldn\'t be set in the database.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }

                        elseif (!$this->set('password', $password)) {
                            FlashMessage::flash('ChangeConnectionSettingsError', 'The Password couldn\'t be set in the database.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }

                        elseif (!$this->set('domainName', $domainName)) {
                            FlashMessage::flash('ChangeConnectionSettingsError', 'The Domain Name couldn\'t be set in the database.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }

                        else {
                            // Successfully connected to Active Directory and added the connetion settings to the database.
                            FlashMessage::flash('ChangeConnectionSettingsMessage', 'The connection to Active Directory was successful and the settings were saved.');
                            header('Location: /settings/connectionsettings.php');
                            exit();
                        }
                    }

                    else {
                        FlashMessage::flash('ChangeConnectionSettingsError', 'The Active Directory connection was not successful.<br />Please check your settings.');
                        header('Location: /settings/connectionsettings.php');
                        exit();
                    }

                }

                else {
                    FlashMessage::flash('ChangeConnectionSettingsError', 'All fields were not filled out.');
                    header('Location: /settings/connectionsettings.php');
                    exit();
                }
            }

            else {
                FlashMessage::flash('ChangeConnectionSettingsError', 'Could not connect to the database.');
                header('Location: /settings/connectionsettings.php');
                exit();
            }
        }

        private function testSettings($DC, $port, $username, $password, $domainName) {
            if (isset($DC) && isset($port) && isset($username) && isset($password) && isset($domainName)) {
                if ($ad_connection = ldap_connect($DC, $port)) {
                    ldap_set_option($ad_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ad_connection, LDAP_OPT_REFERRALS, 0);

                    $ldapUsername = $username . '@' . $domainName;
                    if (@ldap_bind( $ad_connection, $ldapUsername, $password)) {
                        return true;
                    }
                } 
            }
            // default return
            return false;

        }
    }
