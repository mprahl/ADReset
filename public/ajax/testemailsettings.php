<?php
    require_once('../../resources/core/init.php');
    $returnMessage = array();

    if (LoginCheck::isLoggedInAsAdmin()) {
        if (isset($_POST['emailto'])) {
            if (isset($_SESSION['user_name'])) {
                $body = "This is a test email sent by " . $_SESSION['user_name'] . " using ADReset.";
            }
            else {
                $body = "This is a test email by ADReset.";
            }

            try {
                sendEmail($_POST['emailto'], 'ADReset Test Email', $body, true);
                $returnMessage = array(
                    'message' => 'The email was sent successfully!',
                    'success' => true
                );
            }
            catch (Exception $e) {
                $returnMessage = array(
                    'message' => 'The email was not sent due to the following: "' . $e->getMessage() . '"',
                    'success' => false
                );
            }
        }
        
    }
    else {
        $returnMessage = array(
            'message' => 'The email was not sent due to the following: "' . 'You must be logged in as an administrator."',
            'success' => false
        );
    }

    header("Content-Type: application/json", true);
    echo json_encode($returnMessage);
    exit;