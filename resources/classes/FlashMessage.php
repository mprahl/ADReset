<?php
	// This class simplifies the use of 'flash messages', which are messages that appear on the screen when a result occurs but dissapear when the page is refreshed
	// This class was heavily influenced by phpacademy
	Class flashMessage {
		public static function flash($name, $message = '') {
			if (isset($_SESSION[$name])) {
				$message = $_SESSION[$name];
				unset($_SESSION[$name]);
				return $message;
			}
			else {
				$_SESSION[$name] = $message;
			}
		}

		public static function flashIsSet($name) {
			if (isset($_SESSION[$name])) {
				return true;
			}
			else {
				return false;
			}
		}

		public static function displayFlash($messageName, $type='') {
            if (isset($messageName)) {
	            if ($type == 'message'){
		            echo '<div class="col-md-12">';
		                echo '<div class="alert alert-success flash-alert">';
		                    echo '<a href="#" class="close" data-dismiss="alert">&times;</a>' . FlashMessage::flash($messageName);
		                echo '</div><br />';
		            echo '</div>';
		            return true;
	        	}
	        	else {
		    		echo '<div class="col-md-12">';
		                echo '<div class="alert alert-danger flash-alert">';
		                    echo '<a href="#" class="close" data-dismiss="alert">&times;</a>' . FlashMessage::flash($messageName);
		                echo '</div><br />';
		            echo '</div>';
		            return true;
	        	}
        	}
        	return false;
		}

		public static function runJsScript($messageName) {
            if (isset($messageName) && isset($_SESSION[$messageName])) {
	           echo '<script src="', $_SESSION[$messageName], '"></script>';
	           unset($_SESSION[$messageName]);
	           return true;
        	}
        	return false;
		}
	}
