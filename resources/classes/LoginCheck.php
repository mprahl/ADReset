<?php
    Class LoginCheck {
    	public static function isLoggedIn() {
    		@session_start();
            if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1) {
                return true;
            }
            // default return
            return false;
        }

        public static function isLoggedInAsAdmin() {
    		@session_start();
            if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1 && isset($_SESSION['privilege']) && $_SESSION['privilege'] == 'admin') {
                return true;
            }
            // default return
            return false;
    	}

    	public static function isDomain() {
    		@session_start();
            if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1 && isset($_SESSION['auth_source']) && $_SESSION['auth_source'] == 'domain') {
                return true;
            }
            // default return
            return false;
    	}

        public static function isDomainNormalUser() {
            @session_start();
            if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1 && isset($_SESSION['auth_source']) && $_SESSION['auth_source'] == 'domain' &&  isset($_SESSION['privilege']) && $_SESSION['privilege'] == 'user') {
                return true;
            }
            // default return
            return false;
        }

    	public static function isLocal() {
    		@session_start();
            if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1 && isset($_SESSION['auth_source']) && $_SESSION['auth_source'] == 'local') {
                return true;
            }
            // default return
            return false;
    	}
    }
