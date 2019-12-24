<?php

// The SQLite3 database file
define('DATABASE_FILE', 'database.sqlite3');

// The number of decimal that have to be the same when rounded to count as a tie
define('RANKING_PRECISION', 3);

// Used in creating the database
define('DEFAULT_USERNAME', 'user'); // max length of 250 characters
define('DEFAULT_PASSWORD', 'password'); // max length of 250 characters

define('APPLICATION_NAME', 'Garnet DeGelder\'s Score Tracker');
define('APPLICATION_VERSION', '2.0.0-dev');
define('APPLICATION_COPYRIGHT_HTML', 'Copyright &copy; 2019 Garnet DeGelder');
