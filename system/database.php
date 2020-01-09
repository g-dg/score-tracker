<?php

require_once('system/config.php');

// check if the database is accessible
if (!is_file(DATABASE_FILE) ||
	!is_readable(DATABASE_FILE) ||
	!is_writable(DATABASE_FILE) ||
	!is_readable(dirname(DATABASE_FILE)) ||
	!is_writable(dirname(DATABASE_FILE))) {
	throw new Exception('The database is inaccessible (must be readable and writable and in a readable and writable directory)');
}
// connect to the database
$database_connection = new PDO('sqlite:' . DATABASE_FILE);
$database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database_connection->setAttribute(PDO::ATTR_TIMEOUT, 60);
// use write-ahead logging for performance reasons
database_query('PRAGMA journal_mode=WAL;');
database_query('PRAGMA synchronous=NORMAL;');
// enable foreign key constraints
database_query('PRAGMA foreign_keys = ON;');

// check if the tables exist
if (count(database_query('SELECT "name" FROM "sqlite_master" WHERE "type" = "table" AND "name" in (\'users\', \'api_tokens\', \'clubs\', \'years\', \'competitions\', \'club_participation\', \'year_competitions\', \'events\', \'teams\', \'competition_events\', \'point_scores\', \'timed_scores\', \'individual_scores\');')) < 12) {
	// create the tables
	try {
		$database_connection->beginTransaction();
		$database_connection->exec('DROP TABLE IF EXISTS "individual_scores";DROP TABLE IF EXISTS "timed_scores";DROP TABLE IF EXISTS "point_scores";DROP TABLE IF EXISTS "competition_events";DROP TABLE IF EXISTS "teams";DROP TABLE IF EXISTS "events";DROP TABLE IF EXISTS "year_competitions";DROP TABLE IF EXISTS "club_participation";DROP TABLE IF EXISTS "competitions";DROP TABLE IF EXISTS "years";DROP TABLE IF EXISTS "clubs";DROP TABLE IF EXISTS "api_tokens";DROP TABLE IF EXISTS "users";CREATE TABLE "users"("id" INTEGER PRIMARY KEY,"username" TEXT NOT NULL UNIQUE,"password" TEXT NOT NULL,"type" TEXT NOT NULL DEFAULT \'user\',"enabled" INTEGER NOT NULL DEFAULT 1);CREATE TABLE "api_tokens"("id" INTEGER PRIMARY KEY,"token" TEXT NOT NULL UNIQUE,"user_id" INTEGER NOT NULL REFERENCES "users" ON DELETE CASCADE,"expires" INTEGER NOT NULL DEFAULT(STRFTIME(\'%s\',\'now\')+ 3600));CREATE TABLE "clubs"("id" INTEGER PRIMARY KEY,"name" TEXT NOT NULL UNIQUE);CREATE TABLE "years"("id" INTEGER PRIMARY KEY,"name" TEXT NOT NULL UNIQUE);CREATE TABLE "competitions"("id" INTEGER PRIMARY KEY,"name" TEXT NOT NULL UNIQUE,"overall_point_multiplier" REAL NOT NULL DEFAULT 1.0);CREATE TABLE "club_participation"("id" INTEGER PRIMARY KEY,"year_id" INTEGER NOT NULL REFERENCES "years","club_id" INTEGER NOT NULL REFERENCES "clubs",UNIQUE("year_id","club_id"));CREATE TABLE "year_competitions"("id" INTEGER PRIMARY KEY,"year_id" INTEGER NOT NULL REFERENCES "years","competition_id" INTEGER NOT NULL REFERENCES "competitions",UNIQUE("year_id","competition_id"));CREATE TABLE "events"("id" INTEGER PRIMARY KEY,"name" TEXT NOT NULL,"competition_id" INTEGER NOT NULL REFERENCES "competitions","type" TEXT NOT NULL DEFAULT \'points\',"overall_point_multiplier" REAL NOT NULL DEFAULT 1.0,"timed_min_time" REAL NOT NULL,"timed_max_time" REAL NOT NULL,"timed_max_points" REAL NOT NULL,"timed_error_penalty_time" REAL NOT NULL,"timed_cap_points" INTEGER NOT NULL DEFAULT 1,UNIQUE("name","competition_id"));CREATE TABLE "teams"("id" INTEGER PRIMARY KEY,"name" TEXT NOT NULL,"club_participation_id" INTEGER NOT NULL REFERENCES "club_participation","year_competition_id" INTEGER NOT NULL REFERENCES "year_competitions",UNIQUE("name","club_participation_id"));CREATE TABLE "competition_events"("id" INTEGER PRIMARY KEY,"year_competition_id" INTEGER NOT NULL REFERENCES "year_competitions","event_id" INTEGER NOT NULL REFERENCES "events",UNIQUE("year_competition_id","event_id"));CREATE TABLE "point_scores"("id" INTEGER PRIMARY KEY,"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events","team_id" INTEGER NOT NULL REFERENCES "teams","points" REAL NOT NULL,UNIQUE("competition_event_id","team_id"));CREATE TABLE "timed_scores"("id" INTEGER PRIMARY KEY,"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events","team_id" INTEGER NOT NULL REFERENCES "teams","time" REAL NOT NULL,"errors" REAL NOT NULL,UNIQUE("competition_event_id","team_id"));CREATE TABLE "individual_scores"("id" INTEGER PRIMARY KEY,"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events","team_id" INTEGER NOT NULL REFERENCES "teams","name" TEXT NOT NULL,"points" REAL NOT NULL,UNIQUE("competition_event_id","team_id"));');
		$database_connection->commit();
	} catch (Exception $e) {
		$database_connection->rollBack();
		throw new Exception('Could not set up the database. (The database must be readable and writable and in a readable and writable directory)', 0, $e);
	}
}

// check if there is at least one user
$database_connection->beginTransaction();
if ((int)database_query('SELECT COUNT() FROM "users";')[0][0] < 1) {
	database_query('INSERT INTO "users"("username","password","type","enabled")VALUES(?,?,\'administrator\',1);', [DEFAULT_USERNAME, password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT)]);
}
$database_connection->commit();

/**
 * Executes an SQL query on the database
 * @param sql The SQL statement
 * @param params The parameters to pass to the SQL statement
 * @return array The result set
 */
function database_query($sql, $params = [])
{
	global $database_connection;
	$done_retrying = false;
	$start_time = time();
	while (!$done_retrying) {
		try {
			$stmt = $database_connection->prepare($sql);
			$stmt->execute($params);
			$database_affected_row_count = $stmt->rowCount();
			$done_retrying = true;
		} catch (PDOException $e) {
			// keep retrying if locked
			if (substr_count($e->getMessage(), 'database is locked') == 0) {
				throw new Exception($e->getMessage(), 0, $e);
			} else {
				if (time() - $start_time > 60) {
					throw new Exception($e->getMessage(), 0, $e);
				}
				usleep(mt_rand(1000, 10000));
			}
		}
	}
	return $stmt->fetchAll();
}
