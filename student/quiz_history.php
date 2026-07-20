<?php
session_start();

require_once __DIR__ . '/../includes/auth.php';
require_role('student', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$studentId = current_user_id();
$attempts  = get_attempts_for_student($pdo, $studentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz History - Quiz Master</title>
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
        <h1>Quiz History</h1>
        <p class="mb-0">Every quiz attempt you have made.</p>
    </div>

    <div class="card-box mt-4 mb-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Correct</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($attempts) === 0): ?>
                    <tr>
                        <td colspan="6" class="text-muted">No quiz attempts yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attempts as $attempt): ?>
                        <?php $summary = get_answer_summary($pdo, (int) $attempt['attempt_id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($attempt['quiz_title']) ?></td>
                            <td><?= (int) $attempt['attempt_number'] ?></td>
                            <td><?= (int) $attempt['score'] ?>%</td>
                            <td>
                                <?= $summary['total'] > 0
                                    ? (int) $summary['correct'] . '/' . (int) $summary['total']
                                    : '&mdash;' ?>
                            </td>
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
