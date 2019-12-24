<?php

define('API_TOKEN_LIFETIME', 3600);

require_once('config.php');
require_once('system/session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'], $_POST['csrf_token']) && $_POST['csrf_token'] == $_SESSION['scoretracker.csrf_token']) {

	require_once('system/database.php');

	$result = database_query('SELECT "id", "username", "password", "type", "enabled" FROM "users" WHERE "username" = ?', [$_POST['username']]);

	if (isset($result[0])) {
		// user exists
		if (password_verify($_POST['password'], $result[0]['password'])) {

			// password is correct
			if (password_needs_rehash($result[0]['password'], PASSWORD_DEFAULT)) {
				// rehash password if needed
				database_query('UPDATE "users" SET "password" = ? WHERE "id" = ?;', [password_hash($_POST['password'], PASSWORD_DEFAULT), (int) $result[0]['id']]);
			}

			if ((bool) $result[0]['enabled']) {

				// user is enabled
				$_SESSION['scoretracker.user_id'] = (int) $result[0]['id'];
				$_SESSION['scoretracker.username'] = $result[0]['username'];
				$_SESSION['scoretracker.user_type'] = $result[0]['type'];

				// generate new api token
				$_SESSION['scoretracker.api_token'] = generate_random_string(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
				database_query('INSERT INTO "api_tokens" ("token", "user_id", "expires") VALUES (?, ?, ?);', [$_SESSION['scoretracker.api_token'], $_SESSION['scoretracker.user_id'], time() + API_TOKEN_LIFETIME]);

				header('Location: ' . (isset($_GET['url']) ? $_GET['url'] : 'index.php'));
				exit();

			} else {
				// user disabled
				$_SESSION['scoretracker.login_result'] = 'Account is disabled.';
				header('Location: login.php?url=' . (isset($_GET['url']) ? $_GET['url'] : 'index.php'));
			}
		} else {
			// password incorrect
			$_SESSION['scoretracker.login_result'] = 'Username or password is incorrect.';
			header('Location: login.php?url=' . (isset($_GET['url']) ? $_GET['url'] : 'index.php'));
		}
	} else {
		// username not found
		$_SESSION['scoretracker.login_result'] = 'Username or password is incorrect.';
		header('Location: login.php?url=' . (isset($_GET['url']) ? $_GET['url'] : 'index.php'));
	}
	exit();
} else {
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Sign In - <?= htmlspecialchars(APPLICATION_NAME) ?></title>
	<link rel="stylesheet" href="css/normalize.min.css" />
	<link rel="stylesheet" href="css/login.css" />
</head>
<body>
	<h1><?= htmlspecialchars(APPLICATION_NAME) ?></h1>
	<h2>Sign in</h2>
	<form action="login.php?url=<?= htmlspecialchars(urlencode(isset($_GET['url']) ? $_GET['url'] : 'index.php')) ?>" method="POST">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['scoretracker.csrf_token']) ?>" />
		<label for="username">Username:</label>
		<input type="text" name="username" value="" placeholder="Username" autofocus="autofocus" />
		<label for="password">Password:</label>
		<input type="password" name="password" value="" placeholder="Password" />
		<input type="submit" value="Sign In" />
	</form>
	<?php if (isset($_SESSION['scoretracker.login_result'])){ ?><div class="alert alert-danger"><?= htmlspecialchars($_SESSION['scoretracker.login_result']) ?></div><?php	unset($_SESSION['scoretracker.login_result']);}	?>
</body>
</html>
<?php
}
