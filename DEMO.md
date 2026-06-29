# Quiz Master — Demo Guide

A 3–4 minute walkthrough for the professor. Everything in this
demo runs **client-side** (no MySQL/database setup needed) so it works on any
machine that has PHP.

## Running it

From the project root:

```bash
php -S localhost:8000
```

Then open **http://localhost:8000/** in a browser.

> Why a server (and not just opening the file)? The pages are `.php`, and the
> quiz loads inside an `<iframe>` — both behave correctly when served over
> `http://`. The PHP built-in server is the simplest option; XAMPP/Apache works
> too if you put the folder in `htdocs`.

> No PHP installed? The pages currently have no server-side logic yet, so for a
> pinch you can rename the `.php` files to `.html` and open `index.html`. Using
> `php -S` is the intended path.

## The walkthrough (suggested script)

**1. Landing page** — `http://localhost:8000/`
A role chooser. Point out that students and instructors share one themed app.

**2. Student dashboard** — click **I'm a Student**
- Shows available quizzes (Python Quiz 1 is open; Java Quiz 1 is locked until you
  score ≥ 75%), live stats, and a "Quiz Attempts" table (empty on first run).

**3. Take the quiz** — click **Start Quiz**
- The quiz itself (`quizzes/python/quiz1.html`) is loaded in an **iframe**.
- Answer the questions and click **Submit Quiz** *inside* the quiz.
- The score is sent from the iframe up to the page using the browser's
  **`postMessage`** API. The page catches it, shows your score, and **enables the
  "Record My Score" button** (it starts disabled). This is the core piece of this
  week's deliverable.
- Click **Record My Score** → you land on the results page.

**4. Results + history**
- Results page shows your grade, correct count, and attempt number.
- Back on the **dashboard**, the stats and the Quiz Attempts table now show your
  real attempt. Score ≥ 75% also **unlocks Java Quiz 1**.

**5. Switch to the instructor side** — from the dashboard topbar, or go to
`http://localhost:8000/instructor/dashboard.php`
- The instructor dashboard and **Results** page read the *same* attempt data, so
  the quiz you just took shows up as a submission with the class average. This
  demonstrates the full student → instructor data flow end to end.
- Show **Upload New Quiz** and **Edit Quiz** — the metadata forms (file upload,
  points, attempts, resubmission toggle).

## What's functional vs. placeholder (be upfront with the prof)

**Functional now**
- Themed, responsive UI shared across student + instructor pages.
- Full quiz flow: iframe quiz → `postMessage` score → enable submit → record
  attempt → results/history.
- Attempt unlocks the next quiz at the 75% threshold.
- Instructor dashboard/results reflect real attempts (via browser `localStorage`).

**Placeholder / next iterations**
- Data lives in the browser (`localStorage`) for the demo. The PDO/MySQL layer is
  scaffolded in `includes/db.php` + `sql/quizmaster_schema.sql` and gets wired in
  next, replacing `localStorage` with `submit_score.php` saving to the database.
- Login/roles, quiz upload persistence, and CSV export are upcoming milestones.

## Reset between runs

The demo stores attempts in the browser. To start clean, open DevTools console
and run:

```js
localStorage.clear()
```
