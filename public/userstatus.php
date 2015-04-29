<?php
    // This file is used by the PowerShell script to determine if a user's secret questions are set.
    require_once('../resources/core/init.php');
    if (isset($_GET['username']) && !empty($_GET['username'])) {
        try {
            $AD = new AD();
        }
        catch (Exception $e) {
            $json = array (
                'status' => 'failure'
            );

            echo json_encode($json);
            return false;
        }

        try {
            $resetPW = new ResetPW();
            if ($resetPW->isUserAllowedToReset(urldecode($_GET['username']), $AD)) {
                $userSettings = new UserSettings();
                if ($userSettings->numSecretQuestionsSetToUser(urldecode($_GET['username'])) >= 3) {
                    $json = array (
                        'status' => 'complete'
                    );

                    echo json_encode($json);
                    return true;
                }
                else {
                    $json = array (
                        'status' => 'incomplete'
                    );

                    echo json_encode($json);
                    return true;
                }
            }
            else {
                $json = array (
                        'status' => 'restricted'
                    );

                echo json_encode($json);
                return true;
            }
            
        }
        catch (Exception $e) {
            // If there is an error. Continue to the default return
        }
    }

    $json = array (
        'status' => 'failure'
    );

    echo json_encode($json);
    return false;