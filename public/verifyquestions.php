<?php
	require_once('../resources/core/init.php');

	if (LoginCheck::isLoggedIn()) {
	    header("Location: /index.php");
	    exit();
	}
	else {
		if (isset($_GET['username'])) {
			// Make sure ADReset can connect to Active Directory
	    	try {
	    		$AD = new AD();
	    	}
	    	catch (Exception $e) {
	    		FlashMessage::flash('ResetPWError', sanitize($e->getMessage()));
	    		header("Location: /resetpw.php");
	    		exit();
	    	}

			//Check to make sure there haven't been more than 5 failed attempts
			$systemSettings = new SystemSettings();
			$resetPW = new ResetPW();
			if ($failedAttemptsAllowed = $systemSettings->getOtherSetting('failedattemptsallowed')) {
				if ($resetPW->getNumberOfFailedAttempts($_GET['username']) >= intval($failedAttemptsAllowed)) {
		    		FlashMessage::flash('ResetPWError', 'You have failed to verify your secret questions too many times and your account is locked. Please contact the Help Desk for assistance.');
		    		header("Location: /resetpw.php");
		    		exit();
		    	}
			}
			else {
				FlashMessage::flash('ResetPWError', 'There was a database error. Please try again.');
	    		header("Location: /resetpw.php");
	    		exit();
			}

			// Make sure the user has three questions set
	    	$userSettings = new UserSettings();
	    	try {
	    		if ($userSettings->numSecretQuestionsSetToUser(urldecode($_GET['username'])) < 3) {
	    			FlashMessage::flash('ResetPWError', 'You cannot use this feature because you do not have your secret questions set.');
		    		header("Location: /resetpw.php");
		    		exit();
	    		}
	    	}
	    	catch (Exception $e) {
	    		FlashMessage::flash('ResetPWError', 'There was a database error. Please try again.');
	    		header("Location: /resetpw.php");
	    		exit();
	    	}

	    	// Make sure the user is allowed to reset their password
	    	if ($resetPW->isUserAllowedToReset(urldecode($_GET['username']), $AD)) {
	    		require_once(RESOURCE_DIR . '/views/verify_questions.php');
	    	}
	    	else {
	    		FlashMessage::flash('ResetPWError', 'You are not permitted to use this feature. Please contact the Help Desk for assistance.');
	    		header("Location: /resetpw.php");
	    		exit();
	    	}
	    	
		}
		elseif (isset($_POST['verifySecretQuestions'])) {
			if (isset($_POST['secretQuestion1'], $_POST['secretQuestion2'], $_POST['secretQuestion3'], $_POST['secretAnswer1'], $_POST['secretAnswer2'], $_POST['secretAnswer3'], $_POST['username'])) {
				$userSettings = new UserSettings();

				// Make sure ADReset can connect to Active Directory
				try {
		    		$AD = new AD();
		    	}
		    	catch (Exception $e) {
		    		FlashMessage::flash('ResetPWError', sanitize($e->getMessage()));
		    		header("Location: /resetpw.php");
		    		exit();
		    	}

		    	$resetPW = new resetPW();
		    	// Make sure the user is allowed to reset their password
		    	if (!$resetPW->isUserAllowedToReset($_POST['username'], $AD)) {
		    		FlashMessage::flash('ResetPWError', 'You are not permitted to use this feature. Please contact the Help Desk for assistance.');
		    		header("Location: /resetpw.php");
		    		exit();
		    	}

		    	//Check to make sure there haven't been more than 5 failed attempts
		    	$systemSettings = new systemSettings();
				if ($failedAttemptsAllowed = $systemSettings->getOtherSetting('failedattemptsallowed')) {
					if ($resetPW->getNumberOfFailedAttempts($_GET['username']) >= intval($failedAttemptsAllowed)) {
			    		FlashMessage::flash('ResetPWError', 'You have failed to verify your secret questions too many times and your account is locked. Please contact the Help Desk for assistance.');
			    		header("Location: /resetpw.php");
			    		exit();
			    	}
				}
				else {
					FlashMessage::flash('ResetPWError', 'There was a database error. Please try again.');
		    		header("Location: /resetpw.php");
		    		exit();
				}

		    	if ($resetPW->getNumberOfFailedAttempts($_POST['username']) >= 5) {
		    		FlashMessage::flash('ResetPWError', 'You have failed to verify your secret questions too many times and your account is locked. Please contact the Help Desk for assistance.');
		    		header("Location: /resetpw.php");
		    		exit();
		    	}

		    	// Verify the questions and set a temporary code if successful
				if (!empty($_POST['secretQuestion1']) && !empty($_POST['secretQuestion2']) && !empty($_POST['secretQuestion3']) && !empty($_POST['secretAnswer1']) && !empty($_POST['secretAnswer2']) && !empty($_POST['secretAnswer3']) && !empty($_POST['username'])) {
					$userSettings = new UserSettings();
					if ($resetPW->verifySecretQuestionSetToUser($_POST['username'], $_POST['secretQuestion1'], $_POST['secretAnswer1']) && $resetPW->verifySecretQuestionSetToUser($_POST['username'], $_POST['secretQuestion2'], $_POST['secretAnswer2']) && $resetPW->verifySecretQuestionSetToUser($_POST['username'], $_POST['secretQuestion3'], $_POST['secretAnswer3'])) {
						$resetPW = new ResetPW();
						if ($generatedcode = $resetPW->generateQuestionsCode($_POST['username'])) {
							header("Location: /newpw.php?idq=" . $generatedcode);
		    				exit();
						}
					}

					// Record the failed login in the database
					$resetPW->setFailedAttempt($_POST['username']);
					FlashMessage::flash('ResetPWError', 'The secret questions provided were incorrect. Please try again.');
		    		header("Location: /resetpw.php");
		    		exit();
				}
			}

		}
		else {
			header("Location: /resetpw.php");
	    	exit();
		}
	}
