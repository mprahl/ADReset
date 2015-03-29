<?php
	require_once('../resources/core/init.php');

	$login = new ADLogin();
	$systemSettings = new SystemSettings;
	$isEmailResetEnabled = $systemSettings->getOtherSetting('emailresetenabled');

	// If they are logged in then they shouldn't be resetting their password. In this case, redirect them to home.
	if ($login->isUserLoggedIn() == true) {
	    header("location: /index.php");
	    exit();
	}
	elseif (isset($isEmailResetEnabled) && $isEmailResetEnabled == 'true') {
		//If they are requesting an email password reset link, this post will exist
		if (isset($_POST['resetPassword'])) {
			$resetPW = new ResetPW();
			try {
				$resetPW->generateAndSendCode($_POST['user_name']);
			}
			catch (Exception $e) {
				switch ($e->getMessage()) {
					case 'The user does not have permission to use ADReset':
						FlashMessage::flash('ResetPWError', 'You do not have permission to use ADReset. Please contact the Help Desk for assistance.');
						break;
					case 'The Active Directory connection failed':
						FlashMessage::flash('ResetPWError', 'The connection to Active Directory failed. Please contact the Help Desk for assistance.');
						break;
					case 'The email failed to send':
						FlashMessage::flash('ResetPWError', 'The email with your generated link failed to send. Please contact the Help Desk for assistance.');
						break;
					case 'The database connection failed':
						FlashMessage::flash('ResetPWError', 'The database connection failed. Please contact the Help Desk for assistance.');
						break;
					case 'The reset email is not properly set in Active Directory.':
						FlashMessage::flash('ResetPWError', 'The reset email is not properly set in Active Directory. Please contact the Help Desk for assistance.');
						break;
					default:
						FlashMessage::flash('ResetPWError', 'An unexpected error occurred. Please contact the Help Desk for assistance.');
				}
	            header('Location: /resetpwemail.php');
	            exit();
			}

			FlashMessage::flash('ResetPWMessage', 'The password recovery email was sent. You may now close this tab.');
            header('Location: /resetpwemail.php');
            exit();
		}

		require_once(RESOURCE_DIR . "/views/reset_pw_email.php");
	}
	else {
		header("location: /index.php");
	    exit();
	}