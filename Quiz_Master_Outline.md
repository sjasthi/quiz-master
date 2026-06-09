# Quiz Master — Requirements & Project Outline
**Course:** Software Engineering | **Professor:** Siva Jasthi
**Platform:** learnandhelp.com | **Phase:** FP2 Deliverable

---

## 1. Problem Statement

Quizzes for learnandhelp.com currently exist as standalone HTML files hosted on GitHub. The manual workflow is:

1. Instructor shares a GitHub link
2. Student downloads the HTML file and takes the quiz locally
3. Student screenshots their score and uploads it to Google Classroom
4. Instructor manually reviews each screenshot and records a grade

**Pain points:** Screenshots can be faked, grading is manual and time-consuming, there is no record of how many attempts a student took, and the experience is completely disconnected from learnandhelp.com.

---

## 2. Solution Overview

**Quiz Master** is a PHP/MySQL web module integrated into learnandhelp.com that allows students to take quizzes directly in the browser. Scores are captured automatically and saved to the database. Instructors get a dashboard to manage quizzes and view student results — eliminating the screenshot workflow entirely.

---

## 3. Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML, CSS, JavaScript, jQuery, Bootstrap |
| Server | PHP |
| Database | MySQL |
| Hosting | learnandhelp.com (shared hosting, FTP deploy) |
| Quizzes | Existing HTML files from GitHub, served inside an iframe |

---

## 4. User Roles

| Role | Description |
|------|-------------|
| **Student** | Logs in, takes quizzes, views their own scores and history |
| **Instructor** | Uploads quizzes, sets metadata, views all student results |

---

## 5. Requirements

### 5.1 Authentication

- Students and instructors log in through learnandhelp.com's existing login system
- PHP sessions maintain login state across pages
- Students cannot access instructor pages; instructors cannot access other students' data
- Every page checks for a valid session before rendering

### 5.2 Quiz Management (Instructor)

Instructors can upload, edit, and manage quizzes. Each quiz is an HTML file paired with metadata:

| Field | Description |
|-------|-------------|
| Title | Display name, e.g. "Python 101 – Quiz 1" |
| Class name | e.g. "Python 101" — used to show the right quizzes to the right students |
| Total points | Maximum score value, e.g. 100 |
| Allow retakes | Yes or No |
| Max attempts | If retakes are allowed, how many times (blank = unlimited) |
| Due date | Optional — after this date the quiz is locked for students |
| Published | Toggle visibility on/off for students |

Instructors can also deactivate a quiz (hides it from students) or delete it if no submissions exist yet.

### 5.3 Taking a Quiz (Student)

- The student dashboard lists all published quizzes for their class, showing their current status per quiz (not started, score, locked)
- Before loading a quiz, the system checks: Is it published? Has the due date passed? Has the student used all their attempts?
- The quiz HTML file loads inside an `<iframe>` on the quiz page
- When the student finishes the quiz, the score passes from the iframe to the parent page using the browser's `postMessage` API — this requires a small one-time addition to each existing quiz file
- The student clicks "Submit Score" to officially record their result
- The server re-validates all rules before saving (attempt limit, due date, valid session) — the client is never trusted

### 5.4 Scores & Retakes

- Every submission is saved to the database with a timestamp and attempt number
- If retakes are allowed, the student's **best score** is the one reported to the instructor
- A student can check "Accept this score" to lock their submission and stop retaking even if attempts remain
- Retake rules per scenario:

| Setting | Behavior |
|---------|----------|
| Retakes off | One attempt only; quiz locks after first submission |
| Retakes on, no limit | Student can retake as many times as they want |
| Retakes on, max = 3 | Button disables after 3 submissions |
| Student accepts score | Locks retakes regardless of attempts remaining |
| Due date passes | Quiz locks for all students |

### 5.5 Results & History

- After submitting, the student sees their score, percentage, attempt number, and full attempt history
- Students can view a history page listing every quiz they have ever attempted
- Instructors can view a per-quiz results table showing every student's attempts, best score, and whether they accepted a final score
- Instructors see aggregate class stats: completion rate, average score, and score distribution

### 5.6 Score Override

- Instructors can manually override any student's recorded score
- An override requires a written note explaining the reason
- The adjusted score is displayed with an "instructor adjusted" label to distinguish it from the raw submission

### 5.7 CSV Export

- Instructors can download any quiz's results as a `.csv` file
- Format is compatible with Google Classroom grade import

### 5.8 Integration with learnandhelp.com

- Quiz Master lives under the existing site navigation (e.g. a "Quizzes" link in the Learn menu)
- Pages match the existing Bootstrap theme, color scheme, and layout
- No separate account is needed — the existing learnandhelp.com login is reused

---

## 6. Database Schema

### `quizzes`
Stores each quiz's metadata set by the instructor.

| Column | Type | Notes |
|--------|------|-------|
| id | INT PK | |
| instructor_id | INT FK → users.id | |
| title | VARCHAR(255) | |
| class_name | VARCHAR(100) | Matches student's enrolled class |
| description | TEXT | Optional |
| filename | VARCHAR(255) | Stored path to the HTML file |
| total_points | INT | |
| allow_retakes | TINYINT(1) | 0 = no, 1 = yes |
| max_attempts | INT | NULL = unlimited |
| due_date | DATETIME | NULL = no deadline |
| is_active | TINYINT(1) | 0 = draft, 1 = published |
| created_at | DATETIME | |

### `submissions`
One row per student attempt.

| Column | Type | Notes |
|--------|------|-------|
| id | INT PK | |
| quiz_id | INT FK → quizzes.id | |
| student_id | INT FK → users.id | |
| score | INT | Points earned |
| attempt_number | INT | 1, 2, 3… |
| is_final | TINYINT(1) | 1 = student accepted this score |
| submitted_at | DATETIME | |

### `score_overrides`
Tracks instructor-adjusted scores.

| Column | Type | Notes |
|--------|------|-------|
| id | INT PK | |
| submission_id | INT FK → submissions.id | |
| instructor_id | INT FK → users.id | |
| original_score | INT | |
| override_score | INT | |
| note | TEXT | Required |
| overridden_at | DATETIME | |

---

## 7. Project Outline

### Milestone 1 — Foundation
- Set up folder structure on learnandhelp.com server via FTP
- Create MySQL tables (`quizzes`, `submissions`, `score_overrides`)
- Connect to existing `users` table and session system
- Build `db.php` (PDO connection), `auth.php` (session/role checks), `config.php` (credentials, excluded from git)
- Student and instructor dashboards load (empty but functional)
- `.htaccess` rules in place (block PHP execution in quiz file folder, block direct access to config)

### Milestone 2 — Student Takes a Quiz
- Student dashboard lists quizzes for their class with attempt status
- Quiz page loads the HTML file in an iframe with attempt gating
- Add `postMessage` score reporting to existing quiz HTML files (small additive change)
- Parent page captures score via `window.addEventListener`, enables Submit button
- `submit_score.php` validates and saves submission to database
- Results page shows score, percentage, and attempt history

### Milestone 3 — Instructor Tools
- Instructor dashboard lists all quizzes with submission counts and averages
- Quiz upload form: HTML file + all metadata fields
- Quiz edit page: update metadata, toggle published status
- Per-quiz results table: all students, all attempts, best scores
- Score override modal with required note field
- Aggregate class stats (completion rate, average, score distribution)
- CSV export download

### Milestone 4 — Polish & Integration
- All retake policy rules tested and confirmed working
- Due date locking tested
- Quiz Master added to learnandhelp.com navigation
- Visual theme matches existing site throughout
- Mobile responsiveness check
- Final end-to-end walkthrough as both student and instructor
- README updated with setup and deployment instructions

---

## 8. File Structure

```
/quiz-master/
├── index.php                   Student dashboard
├── quiz_take.php               Quiz page (iframe + score capture)
├── quiz_results.php            Post-submission results
├── quiz_history.php            Student's full attempt history
│
├── /instructor/
│   ├── dashboard.php           Instructor overview
│   ├── quiz_upload.php         Upload quiz + set metadata
│   ├── quiz_edit.php           Edit metadata
│   └── quiz_results.php        Per-quiz student results + export
│
├── /api/
│   ├── submit_score.php        Receives and saves student score
│   ├── override_score.php      Instructor score override
│   └── export_csv.php          CSV download handler
│
├── /includes/
│   ├── db.php                  PDO database connection
│   ├── auth.php                Session and role check functions
│   ├── functions.php           Shared helpers (get attempts, best score, etc.)
│   └── config.php              DB credentials — NOT committed to git
│
├── /quizzes/
│   └── /files/                 Uploaded HTML quiz files
│
└── /assets/
    ├── /css/quizmaster.css     Custom styles on top of Bootstrap
    └── /js/quiz_parent.js      postMessage listener + submit button logic
```
