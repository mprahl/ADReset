<?php
    require_once('../resources/classes/Captcha/autoload.php');
    require_once('../resources/core/init.php');
    if (isset($_POST['changepw'])) {
        $changeADPW = new ChangeADPassword();
        $changeADPW->changeFromPOST();
    }

    $builder = new Gregwar\Captcha\CaptchaBuilder;
    $builder->setMaxBehindLines(0);
    $builder->setMaxFrontLines(0);
    $builder->setBackgroundColor(255,255,255);
    $builder->build();
    $_SESSION['changepw_captcha'] = $builder->getPhrase();

    require_once(RESOURCE_DIR . "views/change_pw.php");
