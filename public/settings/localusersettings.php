<?php
require_once('../../resources/core/init.php');

if (isset($_POST['changePassword'])) {
    $changePassword = new ChangePassword();
}
elseif (isset($_POST['changeProfile'])) {
	$userInfo = new UserInfo();
    $userInfo->setProfile($_SESSION['user_name']);
}

if (LoginCheck::isLoggedInAsAdmin()) {
    if (!isset($userInfo)) {
		$userInfo = new UserInfo();
    }
    
    require_once(RESOURCE_DIR . "views/local_admin/usersettings.php");

} else {
    header('Location: /localadmin.php');
    exit();
}