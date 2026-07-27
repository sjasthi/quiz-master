<?php
session_start();

require_once __DIR__ . '/../includes/auth.php';
require_role('student', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$studentId = current_user_id();
$requestedId = isset($_GET['attempt']) ? (int) $_GET['attempt'] : null;

// Every attempt this student has made, across all quizzes.
$allAttempts = get_attempts_for_student($pdo, $studentId);

// Feature the attempt we were redirected to (?attempt=N); otherwise the newest.
$latest = null;
if ($requestedId !== null) {
    foreach ($allAttempts as $attempt) {
        if ((int) $attempt['attempt_id'] === $requestedId) {
            $latest = $attempt;
            break;
        }
    }
}
if ($latest === null && count($allAttempts) > 0) {
    $latest = $allAttempts[count($allAttempts) - 1];
}

// Show that attempt's quiz and its full attempt history for this student.
$quizTitle = $latest['quiz_title'] ?? 'Quiz';
$quizId    = $latest ? (int) $latest['quiz_id'] : 0;
$attempts  = $quizId ? get_attempts_for_student($pdo, $studentId, $quizId) : [];
$summary   = $latest ? get_answer_summary($pdo, (int) $latest['attempt_id']) : null;

// Correct answers: prefer the counts stored on the attempt; fall back to the
// per-question data (older quiz1-style attempts) if they aren't set.
$correctCount = $latest['correct_answers'] ?? null;
$totalCount   = $latest['total_questions'] ?? null;
if (empty($totalCount) && $summary && $summary['total'] > 0) {
    $correctCount = $summary['correct'];
    $totalCount   = $summary['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quizmaster.css?v=1">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <a href="index.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Quiz Results</h1>
        <p class="mb-0">Here is your most recent quiz score.</p>
    </div>

    <div class="card-box mt-4 mb-5">
        <h3><?= htmlspecialchars($quizTitle) ?></h3>

        <?php if ($latest): ?>
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number"><?= (int) $latest['score'] ?>%</div>
                        <p class="mb-0 text-muted">Final Grade</p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number">
                            <?= !empty($totalCount)
                                ? (int) $correctCount . '/' . (int) $totalCount
                                : '&mdash;' ?>
                        </div>
                        <p class="mb-0 text-muted">Correct Answers</p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number">Attempt <?= (int) $latest['attempt_number'] ?></div>
                        <p class="mb-0 text-muted">Latest Attempt</p>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mt-3">
                Your <?= htmlspecialchars($quizTitle) ?> score was saved to the database.
            </div>

            <a href="quiz_take.php?quiz_id=<?= $quizId ?>" class="btn btn-outline-primary mt-3">Retake Quiz</a>
            <a href="index.php" class="btn btn-main mt-3">Return to Dashboard</a>
        <?php else: ?>
            <p class="text-muted">No quiz result found yet.</p>
            <a href="index.php" class="btn btn-main">Return to Dashboard</a>
        <?php endif; ?>
    </div>

    <h3 class="section-title">Attempt History</h3>

    <div class="card-box mb-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($attempts) === 0): ?>
                    <tr>
                        <td colspan="4" class="text-muted">No quiz attempts yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attempts as $attempt): ?>
                        <tr>
                            <td><?= (int) $attempt['attempt_number'] ?></td>
                            <td><?= (int) $attempt['score'] ?>%</td>
                            <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($attempt['submitted_at']))) ?></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($attempt['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
