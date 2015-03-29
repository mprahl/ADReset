<?php
	//This class simplifies getting global configuration settings set in init.php.
	// For example, instead of getting the AD host by using $GLOBALS[config][AD][DC], you can now use, Config::get('AD/DC')
	// This class was heavily influenced by phpacademy
	class Config {
		public static function get($path = null) {
			if($path) {
				$config = $GLOBALS['config'];
				//The explode function will separate the $path string into an array, creating a new element after every '/' character.
				$path = explode('/', $path);
				
				foreach ($path as $configItem) {
					if(isset($config[$configItem])){
						$config = $config[$configItem];
					}
					else {
						$config = 'invalid';
					}
				}
				return $config;
			}
			return false;
		}
	}
