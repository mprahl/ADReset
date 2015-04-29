<?php
require_once('../../resources/core/init.php');

if (LoginCheck::isLoggedInAsAdmin()) {
    if (LoginCheck::isLocal()) {
        $connectiongSettings = new ConnectionSettings();

        if (isset($_POST['ChangeConnectionSettings'])) {
            $connectiongSettings->setWithPost();
        }

        require_once(RESOURCE_DIR ."views/local_admin/connection_settings.php");
    }
    // If the user is not a local admin, don't let them change the connection settings
    else {
        header("Location: /index.php");
        exit();
    }
}

else {
    header('Location: /index.php');
    exit();
}