<?php
	require_once('../resources/classes/Captcha/autoload.php');
	require_once('../resources/core/init.php');
	$systemSettings = new SystemSettings();
	$builder = new Gregwar\Captcha\CaptchaBuilder;
	$builder->setMaxBehindLines(0);
	$builder->setMaxFrontLines(0);
	$builder->setBackgroundColor(255,255,255);
	$builder->build();
	$_SESSION['captcha'] = $builder->getPhrase();

?>

<img src="<?php echo $builder->inline(); ?>" />