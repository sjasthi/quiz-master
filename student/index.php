<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$studentId = get_or_create_demo_student($pdo);
$pythonQuiz = get_or_create_quiz($pdo, 'quizzes/python/quiz1.html', 'Python Quiz 1');
$pythonQuizId = (int) $pythonQuiz['quiz_id'];

$attempts = get_attempts_for_student($pdo, $studentId);

$completedQuizCount = count(array_unique(array_column($attempts, 'quiz_id')));
$currentScore = count($attempts) > 0 ? $attempts[count($attempts) - 1]['score'] . '%' : 'N/A';

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
                <div class="stat-number">1</div>
                <p class="mb-0 text-muted">Available Quiz</p>
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

        <div class="col-md-6 mb-4">
            <div class="card-box">
                <span class="quiz-tag tag-open">Open</span>
                <h4>Python Quiz 1</h4>
                <p class="text-muted">Basic Python data types and syntax.</p>

                <a href="quiz_take.php" class="btn btn-main w-100">
                    Start Quiz
                </a>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card-box">
                <?php if ($javaUnlocked): ?>
                    <span class="quiz-tag tag-open">Open</span>
                    <h4>Java Quiz 1</h4>
                    <p class="text-muted">
                        Unlocked because you passed Python Quiz 1 with at least 75%.
                    </p>
                    <button class="btn btn-main w-100" disabled>
                        Coming Soon
                    </button>
                <?php else: ?>
                    <span class="quiz-tag tag-locked">Locked</span>
                    <h4>Java Quiz 1</h4>
                    <p class="text-muted">
                        Complete Python Quiz 1 with at least 75% to unlock this quiz.
                    </p>
                    <button class="btn btn-secondary w-100" disabled>
                        Locked
                    </button>
                <?php endif; ?>
            </div>
        </div>

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
