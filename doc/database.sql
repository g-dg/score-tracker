PRAGMA foreign_keys = ON;
BEGIN TRANSACTION;

DROP TABLE IF EXISTS "individual_scores";
DROP TABLE IF EXISTS "timed_scores";
DROP TABLE IF EXISTS "point_scores";
DROP TABLE IF EXISTS "competition_events";
DROP TABLE IF EXISTS "teams";
DROP TABLE IF EXISTS "events";
DROP TABLE IF EXISTS "year_competitions";
DROP TABLE IF EXISTS "club_participation";
DROP TABLE IF EXISTS "competitions";
DROP TABLE IF EXISTS "years";
DROP TABLE IF EXISTS "clubs";
DROP TABLE IF EXISTS "auth_tokens";
DROP TABLE IF EXISTS "users";

CREATE TABLE "users" (
	"id" INTEGER PRIMARY KEY,
	"username" TEXT NOT NULL UNIQUE,
	"password" TEXT NOT NULL,
	"type" TEXT NOT NULL DEFAULT 'user' -- administrator, user, read-only
);

CREATE TABLE "auth_tokens" (
	"id" INTEGER PRIMARY KEY,
	"token" TEXT NOT NULL UNIQUE,
	"user_id" INTEGER NOT NULL REFERENCES "users" ON DELETE CASCADE,
	"expires" INTEGER NOT NULL DEFAULT (STRFTIME('%s', 'now') + 3600)
);

CREATE TABLE "clubs" (
	"id" INTEGER PRIMARY KEY,
	"name" TEXT NOT NULL UNIQUE
);

CREATE TABLE "years" (
	"id" INTEGER PRIMARY KEY,
	"name" TEXT NOT NULL UNIQUE
);

CREATE TABLE "competitions" (
	"id" INTEGER PRIMARY KEY,
	"name" TEXT NOT NULL UNIQUE,
	"overall_point_multiplier" REAL NOT NULL DEFAULT 1.0
);

CREATE TABLE "club_participation" (
	"id" INTEGER PRIMARY KEY,
	"year_id" INTEGER NOT NULL REFERENCES "years",
	"club_id" INTEGER NOT NULL REFERENCES "clubs",
	UNIQUE("year_id", "club_id")
);

CREATE TABLE "year_competitions" (
	"id" INTEGER PRIMARY KEY,
	"year_id" INTEGER NOT NULL REFERENCES "years",
	"competition_id" INTEGER NOT NULL REFERENCES "competitions",
	UNIQUE("year_id", "competition_id")
);

CREATE TABLE "events" (
	"id" INTEGER PRIMARY KEY,
	"name" TEXT NOT NULL,
	"competition_id" INTEGER NOT NULL REFERENCES "competitions",
	"type" TEXT NOT NULL DEFAULT 'points', -- points, timed, individual
	"overall_point_multiplier" REAL NOT NULL DEFAULT 1.0,
	"timed_min_time" REAL NOT NULL,
	"timed_max_time" REAL NOT NULL,
	"timed_max_points" REAL NOT NULL,
	"timed_error_penalty_time" REAL NOT NULL,
	"timed_cap_points" INTEGER NOT NULL DEFAULT 1,
	UNIQUE("name", "competition_id")
);

CREATE TABLE "teams" (
	"id" INTEGER PRIMARY KEY,
	"name" TEXT NOT NULL,
	"club_participation_id" INTEGER NOT NULL REFERENCES "club_participation",
	"year_competition_id" INTEGER NOT NULL REFERENCES "year_competitions",
	UNIQUE("name", "club_participation_id")
);

CREATE TABLE "competition_events" (
	"id" INTEGER PRIMARY KEY,
	"year_competition_id" INTEGER NOT NULL REFERENCES "year_competitions",
	"event_id" INTEGER NOT NULL REFERENCES "events",
	UNIQUE("year_competition_id", "event_id")
);

CREATE TABLE "point_scores" (
	"id" INTEGER PRIMARY KEY,
	"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events",
	"team_id" INTEGER NOT NULL REFERENCES "teams",
	"points" REAL NOT NULL,
	UNIQUE("competition_event_id", "team_id")
);

CREATE TABLE "timed_scores" (
	"id" INTEGER PRIMARY KEY,
	"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events",
	"team_id" INTEGER NOT NULL REFERENCES "teams",
	"time" REAL NOT NULL,
	"errors" REAL NOT NULL,
	UNIQUE("competition_event_id", "team_id")
);

CREATE TABLE "individual_scores" (
	"id" INTEGER PRIMARY KEY,
	"competition_event_id" INTEGER NOT NULL REFERENCES "competition_events",
	"team_id" INTEGER NOT NULL REFERENCES "teams",
	"name" TEXT NOT NULL,
	"points" REAL NOT NULL,
	UNIQUE("competition_event_id", "team_id")
);

COMMIT TRANSACTION;
