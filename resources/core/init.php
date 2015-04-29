<?php
    //Start a session as this will be necessary on virtually every page
    session_start();
    
    //Get the web root
    define("RESOURCE_DIR", __DIR__ . "/../");
    define("PUBLIC_DIR", __DIR__ . "/../../public");

    //Declare the database connection and session configuration
    $GLOBALS['config'] = array(
        'mysql' => array(
            'type' => 'mysql',
            'host' => '127.0.0.1',
            'username' => 'adresetuser',
            'password' => '6bX&7UI$ysq!jJUB',
            'db_name' => 'adreset'
        ),

        'security' => array(
            'passwordLength' => 8,
            'encryptionKey' => '%3Q8Eh7gFWaaT#s4F5WJQWBq8d7TFN^R'
        )
    );

    //The spl_autoload_register will automatically include the proper class file when an object is declared of that class
    spl_autoload_register(function($class) {
        require_once (RESOURCE_DIR . 'classes/' . $class . '.php');
    });

    //Function to sanitize user input
    require_once(RESOURCE_DIR . 'functions/sanitize.php');

    //Function to start the database connection
    require_once(RESOURCE_DIR . 'functions/startPDOConnection.php');

    //Function to send emails
    require_once(RESOURCE_DIR . 'functions/sendEmail.php');
