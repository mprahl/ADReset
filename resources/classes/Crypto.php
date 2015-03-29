<?php
	// This class is used to encrypt and decrypt the mail database password stored in the ManageMail database
	Class crypto {
		//Function written by Josh Hartman at http://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
		public static function cust_encrypt($encrypt) {
			if (isset($encrypt)){
				$key = Config::get('security/encryptionKey');
				$encrypt = serialize($encrypt);
				$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
				$key = @pack('H*', $key);
				$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
				$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
				$encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
				return $encoded;
			}

			else {
				return '';
			}
		}

		//Function written by Josh Hartman at http://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
		public static function cust_decrypt($decrypt) {
			if (isset($decrypt)) {
				$key = Config::get('security/encryptionKey');
				$decrypt = explode('|', $decrypt.'|');
			    $decoded = base64_decode($decrypt[0]);
			    $iv = base64_decode($decrypt[1]);
			    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
			    $key = @pack('H*', $key);
			    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
			    $mac = substr($decrypted, -64);
			    $decrypted = substr($decrypted, 0, -64);
			    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
			    if($calcmac!==$mac){ return false; }
			    $decrypted = unserialize($decrypted);
			    return $decrypted;
			}
			else {
				return '';
			}
		}
	}
