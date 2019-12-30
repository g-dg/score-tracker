<?php

define('SESSION_LIFETIME', 3600);

// start session
if (session_status() == PHP_SESSION_NONE){
	session_start();
}

if (!isset($_SESSION['scoretracker.last_access']) || $_SESSION['scoretracker.last_access'] + SESSION_LIFETIME < time()) {
	unset($_SESSION['scoretracker.user_id']);
	unset($_SESSION['scoretracker.username']);
	unset($_SESSION['scoretracker.user_type']);
	unset($_SESSION['scoretracker.api_token']);
	unset($_SESSION['scoretracker.csrf_token']);
	unset($_SESSION['scoretracker.last_access']);
}
$_SESSION['scoretracker.last_access'] = time();

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
