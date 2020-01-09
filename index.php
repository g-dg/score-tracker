<?php

require_once('system/config.php');
require_once('system/session.php');

if (!isset($_SESSION['scoretracker.api_token'])) {
	header('Location: login.php?url=' . urlencode($_SERVER['REQUEST_URI']));
	exit();
}

require_once('system/database.php');

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<link href="css/normalize.min.css" rel="stylesheet" />
	<link href="css/styles.css" rel="stylesheet" />
	<script src="js/jquery.min.js"></script>
	<script src="<?= DEBUG_MODE ? 'js/vue.js' : 'js/vue.min.js' ?>"></script>
	<script src="<?= DEBUG_MODE ? 'js/vue-router.js' : 'js/vue-router.min.js' ?>"></script>
	<title><?= htmlspecialchars(APPLICATION_NAME) ?></title>
</head>
<body>
	
</body>
</html>
