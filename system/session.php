<?php

// start session
if (session_status() == PHP_SESSION_NONE){
	session_start();
}

// used to generate a random string (not cryptographically secure in PHP 5.6)
function generate_random_string($length, $chars)
{
	if (function_exists('random_int')) {
		try {
			$string = '';
			for ($i = 0; $i < $length; $i++) {
				$string .= substr($chars, random_int(0, strlen($chars) - 1), 1);
			}
			return $string;
		} catch (\Exception $e) {
		}
	}
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	return $string;
}

// generate CSRF token if not set (for login)
if (!isset($_SESSION['scoretracker.csrf_token'])){
	$_SESSION['scoretracker.csrf_token'] = generate_random_string(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
}
