# Quiz Format & Score Reporting Contract

Quiz Master shows each quiz inside an `<iframe>` on the quiz page
([student/quiz_take.php](../student/quiz_take.php)). For a quiz to be
**takeable** (i.e. to save a score), it must send its final score up to the
parent page. This document is the contract.

## The message

When the quiz finishes, it posts this to the parent:

```js
window.parent.postMessage({
  quizTitle: "Python Quiz 1",   // display name (parent overrides with the registered title)
  score: 80,                    // percentage 0–100
  correctAnswers: 8,            // number correct
  totalQuestions: 10,           // number of questions
  answers: [                    // optional, per-question detail
    { questionNumber: 1, studentAnswer: "b", correctAnswer: "b", isCorrect: true }
  ]
}, "*");
```

The parent ([assets/js/quiz_parent.js](../assets/js/quiz_parent.js)) reveals the
score, enables **Record My Score**, and POSTs it to
[api/submit_score.php](../api/submit_score.php). The quiz **identity** (which
registered quiz this is) always comes from the parent page, not the iframe, so a
quiz file doesn't need to know its own title or path.

## You usually don't have to add this by hand

Because the existing quizzes come in many formats, we inject a small shim
automatically instead of editing each file:

- **On upload** — [instructor/quiz_upload.php](../instructor/quiz_upload.php)
  runs the injector on every uploaded file.
- **In bulk / offline** — run the batch reformatter:

  ```bash
  php tools/instrument_quizzes.php            # instrument quizzes/python
  php tools/instrument_quizzes.php --dry-run  # preview, change nothing
  ```

The shim (see [includes/quiz_instrument.php](../includes/quiz_instrument.php))
reports a score two ways:

1. **Explicit** — if the quiz calls `QuizMaster.report(correct, total)`, that
   value is used (most reliable).
2. **Auto-capture** — otherwise it watches the page for a final `X / Y`
   (or "You scored N%") shown in an element whose `id`/`class` contains
   `score`/`result`/`grade`, and reports that.

Instrumented files carry an `<!-- quizmaster:instrumented -->` marker, so the
tools are safe to re-run (they skip files that already report a score).

## When auto-capture can't find the score

Some files (e.g. pure "playbook"/tips pages with no graded quiz) have no score
to capture. `instrument_quizzes.php` lists these under **"Needs manual review."**
To fix one, add an explicit call at the moment the quiz completes:

```js
QuizMaster.report(correctCount, totalCount);
```
