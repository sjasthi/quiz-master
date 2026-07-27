<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;
$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;

$submissions = $quiz ? qm_quiz_submissions($pdo, $quizId) : [];
$count = count($submissions);
$average = $count > 0
    ? (int) round(array_sum(array_column($submissions, 'score')) / $count)
    : null;
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
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
</div>
<div class="container">

    <?php if (!$quiz): ?>
        <div class="alert alert-warning mt-5">
            That quiz could not be found. <a href="dashboard.php">Back to dashboard</a>.
        </div>
    <?php else: ?>
        <div class="hero">
            <h1>Quiz Results</h1>
            <p class="mb-0">Student submissions for <strong><?= htmlspecialchars($quiz['title']) ?></strong>.</p>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="card-box">
                    <div class="stat-number"><?= $count ?></div>
                    <p class="mb-0 text-muted">Submissions</p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card-box">
                    <div class="stat-number"><?= $average === null ? 'N/A' : $average . '%' ?></div>
                    <p class="mb-0 text-muted">Average Score</p>
                </div>
            </div>
        </div>

        <div class="card-box mb-5">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Attempt</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($count === 0): ?>
                            <tr>
                                <td colspan="5" class="text-muted">No submissions yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_name']) ?></td>
                                    <td><?= (int) $s['attempt_number'] ?></td>
                                    <td><?= (int) $s['score'] ?>%</td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($s['status']) ?></span></td>
                                    <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($s['submitted_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
