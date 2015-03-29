<?php
	require_once('../resources/core/init.php');

	if (LoginCheck::isLoggedIn()) {
	    header("Location: /index.php");
	    exit();
	}
	else {
		if (isset($_POST['resetPasswordWithQuestions']) && isset($_POST['user_name'])) {
	    	header("Location: /verifyquestions.php?username=" . urlencode(trim($_POST['user_name'])));
	    	exit();
		}

		require_once(RESOURCE_DIR . "/views/reset_pw.php");
	}