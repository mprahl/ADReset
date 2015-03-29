<?php
	// This class logs in the format of "Client IP - Date - Message Type - Message" 
	class Logger {
		public static function log ($messageType, $message, $fileName = "") {
			if (empty($fileName)) {
				 $fileName = __DIR__ . "/../logs/log.txt";
			}

			if (isset($fileName) && isset($message) && isset($messageType)) {
				$messageToLog = $_SERVER['REMOTE_ADDR'] . ' - ' . date ("Y-m-d H:i:s") . ' - ' . $messageType . ' - ' . $message . "\n";
				
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
