Garnet DeGelder's Score Tracker
===============================

About
-----

This is a simple score tracker, created to replace a complex spreadsheet. It supports:
- multiple clubs with multiple teams
- multiple competitions with multiple events
- a simple mobile-friendly score input and a "everything on one page" score overview and editor
- per-event rankings
- per-team rankings
- overall rankings (per-club, shows which clubs did better overall)
- per-club event rankings (shows what needs to be worked on in each club)
- point multipliers on events for overall calculations (can be set to zero if an event should not count in the overall results)

Installation Instructions
-------------------------

1. Edit config.php to specify a SQLite3 database file that you wish to use
2. Change the initial username and/or password set in config.php for security
3. Create the database file (ensure the server process has read-write access to both the file and the directory it is in.)
4. Open a web browser to the application's location
5. Log in with the username and password set in the configuration file
