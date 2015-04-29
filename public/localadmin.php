<?php
    require_once('../resources/core/init.php');

    $login = new Login();

    if (LoginCheck::isLoggedIn() && LoginCheck::isLocal()) {
        // If the AD connection settings are set, then send them to system settings, otherwise, send them connection to settings.
        $ADConnectionSettingsObject = new ConnectionSettings();
        $ADConnectionSettings = $ADConnectionSettingsObject->getAll();
        // Make sure all the settings are set
        if (empty($ADConnectionSettings) || !$ADConnectionSettingsObject->areAllSettingsSet()) {
            header('Location: /settings/connectionsettings.php');
        }
        else {
            header('Location: /settings/systemsettings.php');
        }
    }
    elseif (LoginCheck::isLoggedIn() && LoginCheck::isDomain()) {
        $login->logout();
    }
    else {
        require_once(RESOURCE_DIR . "/views/local_admin/not_logged_in.php");
    }