<?php
	function checkPHPVersion() {
		// Check PHP Version
		if (floatval(substr(phpversion(), 0, 3)) < 5.5) {
			echo '<p>The PHP version of 5.5.0 or greater is required.</p>';
		}
	}

	function checkModulesRequired() {
		// The required modules to run ADReset
		$modulesRequired = array(
			'Core',
			'PDO',
			'bcmath',
			'date',
			'filter',
			'hash',
			'json',
			'ldap',
			'mbstring',
			'mcrypt',
			'openssl',
			'pcre',
			'session',
			'SPL',
			'standard',
			'gd'
		);

		// Check if the required modules are loaded
		foreach ($modulesRequired as $module) {
			if (!extension_loaded($module)) {
				$modulesNotInstalled[] = $module;
			}
		}

		if (isset($modulesNotInstalled)) {
			return $modulesNotInstalled;
		}
		else{
			return array();
		}
	}

	function generateInit($db_hostname, $db_username, $db_password, $db_dbname) {
		if (isset($db_hostname, $db_username, $db_password, $db_dbname)) {
			$initPHP = 
'
<?php
	//Start a session as this will be necessary on virtually every page
	session_start();
	
	//Get the web root
	define("RESOURCE_DIR", __DIR__ . "/../");
	define("PUBLIC_DIR", __DIR__ . "/../../public");

	//Declare the database connection and session configuration
	$GLOBALS[\'config\'] = array(
		\'mysql\' => array(
			\'type\' => \'mysql\',
			\'host\' => \'' . $db_hostname . '\',
			\'username\' => \'' . $db_username . '\',
			\'password\' => \'' . $db_password . '\',
			\'db_name\' => \'' . $db_dbname . '\'
		),

		\'security\' => array(
			\'passwordLength\' => 8,
			\'encryptionKey\' => \'' . substr(md5(rand()), 0, 32) . '\'
		)
	);

	//The spl_autoload_register will automatically include the proper class file when an object is declared of that class
	spl_autoload_register(function($class) {
		require_once (RESOURCE_DIR . \'classes/\' . $class . \'.php\');
	});

	//Function to sanitize user input
	require_once(RESOURCE_DIR . \'functions/sanitize.php\');

	//Function to start the database connection
	require_once(RESOURCE_DIR . \'functions/startPDOConnection.php\');

	//Functio to send emails
	require_once(RESOURCE_DIR . \'functions/sendEmail.php\');
';
			return $initPHP;
		}

		else {
			return '';
		}
	}
