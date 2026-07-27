<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;
$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;

$iframeSrc = $quiz ? '../' . ltrim($quiz['html_file_path'], '/') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $quiz ? htmlspecialchars($quiz['title']) : 'Quiz Preview' ?> - Quiz Master</title>
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
            <h1><?= htmlspecialchars($quiz['title']) ?></h1>
            <p class="mb-0">
                Instructor preview — this is exactly what students see. Scores are not
                recorded here.
            </p>
        </div>

        <iframe
            class="quiz-frame mt-4"
            style="min-height:700px;"
            src="<?= htmlspecialchars($iframeSrc) ?>"
            title="<?= htmlspecialchars($quiz['title']) ?>">
        </iframe>

        <div class="mb-5"></div>
    <?php endif; ?>

</div>

</body>
</html>
