# Quiz Master

A web app where instructors upload/manage quizzes and students take them, with
scores saved to a database. Quizzes are existing standalone HTML files shown in
an iframe; each reports its score to the page via `postMessage`.

**Front End:** Ali & Jama · **Back End:** Salaman & Abdi

---

## Tech stack
- PHP 8 (built-in server for local dev; Apache on learnandhelp.com)
- MySQL / MariaDB (PDO)
- Bootstrap 5 + a shared theme in `assets/css/quizmaster.css`

## Requirements
- PHP 8.0+ with the PDO MySQL extension (XAMPP includes both)
- MySQL 8 or MariaDB 10.4+

## Setup

1. **Get the code** and open a terminal in the project root.

2. **Create your local config.** Copy the template and fill in your values:
   ```bash
   cp includes/config.sample.php includes/config.php
   ```
   `includes/config.php` is git-ignored (never commit real credentials). Set:
   - `DB_HOST`, `DB_NAME` (`quiz_master`), `DB_USER`, `DB_PASS`, `DB_CHARSET`
   - `INSTRUCTOR_SIGNUP_CODE` — the secret required to register an instructor
     account (students don't need it).

3. **Create the database and tables.** Import the schema:
   ```bash
   mysql -u root -p < sql/quizmaster_schema.sql
   ```
   (Or run the file in phpMyAdmin.) It creates the `quiz_master` database and the
   `users`, `quizzes`, `quiz_attempts`, and `student_answers` tables.

4. **Run the app** from the project root:
   ```bash
   php -S localhost:8000
   ```
   Open <http://localhost:8000/>.

## Using it

1. Go to `/register.php` and create a **student** account, and (using your
   `INSTRUCTOR_SIGNUP_CODE`) an **instructor** account.
2. As the instructor: **Upload New Quiz → Import quizzes from folder** registers
   every quiz in `quizzes/python/` at once. You can also upload a single `.html`.
3. As a student: pick a quiz, take it, and click **Record My Score**. Scores show
   up on your dashboard/history and on the instructor's results pages.

### Quiz score reporting
Quizzes report their score with `postMessage`. Existing quiz files are
instrumented automatically (on upload, and in bulk by
`php tools/instrument_quizzes.php`). See [docs/QUIZ_FORMAT.md](docs/QUIZ_FORMAT.md)
for the contract. Instructors can set a **due date** per quiz (upload/edit form);
after it passes, the quiz is closed and submissions are rejected.

## Troubleshooting: "Access denied" / port 3306 in use
If a standalone **MySQL 8** service already owns port 3306 and you want to use
XAMPP's MariaDB instead, either stop the `MySQL80` service (admin) so XAMPP can
use 3306, **or** run MariaDB on another port and point `config.php` at it, e.g.
`define('DB_HOST', '127.0.0.1;port=3307');`. On this project's dev machine we use
3307 — `tools/start_db.bat` starts MariaDB there. See [DEMO.md](DEMO.md).

## Deployment (learnandhelp.com)
1. Upload the project via FTP into the site's web directory.
2. Create `includes/config.php` on the server with the live DB credentials
   (do **not** upload your local one).
3. Import `sql/quizmaster_schema.sql` into the live database.
4. Ensure the `quizzes/uploads/` directory is writable by the web server.
5. In `includes/db.php`, switch the connection error to a generic message and log
   the real error server-side before going live.

---

## Weekly Plan

+ **6/15/2026**: Database Schema & Role Assignment
+ **6/22/2026**: Develop MySQL Tables, connect user tables, build the php files, make Student/Instructor pages load
+ **6/29/2026**: Student dashboard lists quizzes and attempts, add postMessage score reporting to quiz HTML files, enable submit button
+ **7/6/2026**: submit_score.php validates and saves submissions; results page shows score, percentage, and attempt history
+ **7/13/2026**: Instructor dashboard lists all quizzes with submission counts and averages; quiz upload form; quiz edit page; per-quiz results table
+ **7/20/2026**: Score override modal with required note field; aggregate class stats; CSV export download
+ **7/27/2026**: Retake policy rules tested; due-date locking tested; added to learnandhelp.com navigation; visual theme matches site; mobile responsiveness check; final end-to-end walkthrough; README updated with setup and deployment instructions
