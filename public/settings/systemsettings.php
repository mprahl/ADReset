<?php
    require_once('../../resources/core/init.php');
    if (LoginCheck::isLoggedInAsAdmin()) {
        // If the AD connection settings are not set, then tell them the error.
        $ADConnectionSettingsObject = new ConnectionSettings();
        $ADConnectionSettings = $ADConnectionSettingsObject->getAll();
        // Make sure all the settings are set
        if (empty($ADConnectionSettings) || !$ADConnectionSettingsObject->areAllSettingsSet()) {
            echo '<h2 style="text-align:center">Active Directory Connection Settings Error:</h2><h3 style="text-align:center">Please configure your <a href="/settings/connectionsettings.php">AD Connection Settings</a> before configuring System Settings.</h3>';
            Logger::log ('error', 'The database could not retrieve the connection settings for Active Directory');
            exit();
        }

        $systemSettings = new SystemSettings();
        
        if (isset($_POST['addAdminGroup'])) {
            $systemSettings->addAdminGroupWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['deleteAdminGroup'])) {
            $systemSettings->deleteAdminGroupWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['addResetGroup'])) {

            $systemSettings->addResetGroupWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['deleteResetGroup'])) {
            $systemSettings->deleteResetGroupWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['changeEmailSettings'])) {
            $systemSettings->setEmailSettingsWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['updateOtherSettings'])) {
            $systemSettings->setOtherSettingsWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['addSecretQuestion'])) {
            $systemSettings->addSecretQuestionWithPost();
            header('Location: systemsettings.php');
            exit();
        }
        elseif (isset($_POST['changeSecretQuestion'])) {
            $systemSettings->changeSecretQuestionStatusFromPOST();
            header('Location: systemsettings.php');
            exit();
        }

        $emailSettings = $systemSettings->getEmailSettings();
        require_once(RESOURCE_DIR ."views/system_settings.php");
    }

    else {
        header('Location: /index.php');
        exit();
    }