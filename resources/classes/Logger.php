<?php
    // This class logs in the format of "Client IP - Date - Message Type - Message" 
    class Logger {
        public static function log ($messageType, $message, $fileName = "") {
            if (empty($fileName)) {
                 $fileName = __DIR__ . "/../logs/log.txt";
            }

            if (isset($fileName) && isset($message) && isset($messageType)) {
                if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
                    $messageToLog = array(
                        'clientip' => $_SERVER['REMOTE_ADDR'],
                        'time' =>  date ("Y-m-d H:i:s"),
                        'user' => $_SESSION['user_name'],
                        'messagetype' => $messageType,
                        'message' => $message
                    );
                }
                else {
                    $messageToLog = array(
                        'clientip' => $_SERVER['REMOTE_ADDR'],
                        'time' =>  date ("Y-m-d H:i:s"),
                        'messagetype' => $messageType,
                        'message' => $message
                    );
                }

                $messageToLog = json_encode($messageToLog) . "\n";
                
                if ($file = fopen ($fileName, 'a')) {
                    if (flock ($file, LOCK_EX)) {
                        fwrite ($file, $messageToLog);
                        flock ($file, LOCK_UN);
                        fclose ($file);
                        return true;
                    }
                }
            }

            return false;
        }
    }
