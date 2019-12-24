<?php

require_once('config.php');
require_once('system/session.php');

unset($_SESSION['scoretracker.user_id']);
unset($_SESSION['scoretracker.username']);
unset($_SESSION['scoretracker.user_type']);
unset($_SESSION['scoretracker.api_token']);
unset($_SESSION['scoretracker.csrf_token']);

header('Location: login.php');
