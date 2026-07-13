<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$studentId = get_or_create_demo_student($pdo);

// Create or retrieve the sample quiz first.
$pythonQuiz = get_or_create_quiz(
    $pdo,
    'quizzes/python/quiz1.html',
    'Python Quiz 1'
);

$pythonQuizId = (int) $pythonQuiz['quiz_id'];

// Now load all quizzes.
$allQuizzes = qm_all_quizzes($pdo);

$attempts = get_attempts_for_student($pdo, $studentId);

$completedQuizCount = count(array_unique(array_column($attempts, 'quiz_id')));
$currentScore = count($attempts) > 0
    ? $attempts[count($attempts) - 1]['score'] . '%'
    : 'N/A';

$bestPythonScore = get_best_score($pdo, $studentId, $pythonQuizId);
$javaUnlocked = $bestPythonScore !== null && $bestPythonScore >= 75;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quizmaster.css?v=1">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <div>Student Dashboard</div>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Welcome back, Student</h1>
        <p class="mb-0">Start a quiz, check your score, and track your progress.</p>
    </div>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number"><?= count($allQuizzes) ?></div>
                <p class="mb-0 text-muted">Available Quizzes</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number"><?= $completedQuizCount ?></div>
                <p class="mb-0 text-muted">Completed Quiz</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number"><?= htmlspecialchars($currentScore) ?></div>
                <p class="mb-0 text-muted">Current Grade</p>
            </div>
        </div>
    </div>

    <h3 class="section-title">Available Quizzes</h3>

    <div class="row">
        <?php if (count($allQuizzes) === 0): ?>
            <div class="col-12">
                <p class="text-muted">No quizzes available yet. Ask your instructor to add some.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allQuizzes as $q): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card-box h-100 d-flex flex-column">
                        <span class="quiz-tag tag-open">Open</span>

                        <h4><?= htmlspecialchars($q['title']) ?></h4>

                        <p class="text-muted">
                            <?= htmlspecialchars($q['class_name'] ?? 'Python 101') ?>
                        </p>

                        <a href="quiz_take.php?quiz_id=<?= (int) $q['quiz_id'] ?>"
                           class="btn btn-main w-100 mt-auto">
                            Start Quiz
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h3 class="section-title">Quiz Attempts</h3>

    <div class="card-box mb-5">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($attempts) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-muted">No quiz attempts yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attempts as $attempt): ?>
                        <tr>
                            <td><?= htmlspecialchars($attempt['quiz_title']) ?></td>
                            <td><?= (int) $attempt['attempt_number'] ?></td>
                            <td><?= (int) $attempt['score'] ?>%</td>
                            <td><?= htmlspecialchars(date('M j, Y', strtotime($attempt['submitted_at']))) ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?= htmlspecialchars($attempt['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>