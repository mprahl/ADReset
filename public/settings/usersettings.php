<?php
require_once('../../resources/core/init.php');
if (LoginCheck::isDomainNormalUser()) {
    $userSettings = new UserSettings();

    if (isset($_POST['addSecretAnswer'])) {
        $userSettings->addSecretQuestionWithPost();
    }
    elseif (isset($_POST['editSecretAnswer'])) {
        $userSettings->editSecretQuestionWithPost();
    }
    elseif (isset($_POST['resetSecretQuestions'])) {
        $userSettings->resetSecretQuestionsForUser($_SESSION['user_name']);
    }

    require_once(RESOURCE_DIR ."views/user_settings.php");
}
else {
    header('Location: /account.php?page=settings/usersettings.php');
    exit();
}