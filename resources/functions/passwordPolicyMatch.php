<?php
	require_once(__DIR__ . '/../core/init.php');
	function passwordPolicyMatch($password) {
		if (isset($password)){
			if (strlen($password) >= Config::get('security/passwordLength')) {
				return true;
			}
		}

		return false;
	}

	function passwordPolicyWritten() {
		return 'The password must be ' . Config::get('security/passwordLength') . ' or more characters.';
	}
