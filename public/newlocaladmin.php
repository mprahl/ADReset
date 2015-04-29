<?php
    require_once('../resources/core/init.php');

    if (LoginCheck::isLoggedInAsAdmin()) {
        $registration = new NewLocalAdmin();

        if (isset($_POST["register"])) {
            $registration->registerNewUser();
        }

        include(RESOURCE_DIR . "/views/new_local_admin.php");
    }
    else {
        header('Location: /index.php');
        exit();
    }