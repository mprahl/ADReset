<?php
	// A function to sanitize user input when outputted to the screen
	function sanitize($string) {
		$string = trim($string);
		$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
		return $string;
	}
