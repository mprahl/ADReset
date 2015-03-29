<?php
	require_once('../resources/core/init.php');

	$login = new ADLogin();
	$systemSettings = new SystemSettings();
	if (LoginCheck::isLoggedInAsAdmin()) {
	   header('Location: /settings/systemsettings.php');

	} 
	elseif ($systemSettings->getNumOfSecretQuestions() < 3) {
		FlashMessage::flash('LoginError', 'The Administrator is not done configuring ADReset.<br />Please try again later.');
		Logger::log('error', 'The user cannot login because the minimum of three secret questions are not set yet.');
		require_once(RESOURCE_DIR . "views/not_logged_in.php");
	}
	elseif (LoginCheck::isLoggedIn()) {
	    header('Location: /settings/usersettings.php');
	} 
	else {
	    require_once(RESOURCE_DIR . "views/not_logged_in.php");
	}