<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$rows = [];
$dbError = null;

try {
    $rows = qm_quiz_overview($pdo);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$totalQuizzes = count($rows);
$totalSubs = 0;
$weightedScore = 0;

foreach ($rows as $r) {
    $submissions = (int) $r['submissions'];
    $averageScore = (int) $r['average_score'];

    $totalSubs += $submissions;
    $weightedScore += $submissions * $averageScore;
}

$overallAvg = $totalSubs > 0
    ? round($weightedScore / $totalSubs)
    : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Instructor Dashboard - Quiz Master</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/quizmaster.css?v=1"
    >
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">

        <div class="brand">
            Quiz Master
        </div>

        <div>
            <a
                href="../student/index.php"
                class="btn btn-outline-secondary btn-sm"
            >
                Student View
            </a>

            <span class="ms-2">
                Instructor Dashboard
            </span>
        </div>

    </div>
</div>

<div class="container">

    <div class="hero d-flex justify-content-between align-items-center flex-wrap">

        <div>
            <h1>Welcome back, Instructor</h1>

            <p class="mb-0">
                Manage your quizzes and review student results.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap mt-3 mt-md-0">

            <a
                href="student_progress.php"
                class="btn btn-outline-light btn-lg"
            >
                Student Progress
            </a>

            <a
                href="quiz_upload.php"
                class="btn btn-outline-light btn-lg"
            >
                + Upload New Quiz
            </a>

        </div>

    </div>

    <?php if ($dbError): ?>

        <div class="alert alert-danger mt-4">
            Database not reachable:
            <?= htmlspecialchars($dbError) ?>
        </div>

    <?php endif; ?>

    <div class="row mt-4">

        <div class="col-md-4 mb-3">
            <div class="card-box">

                <div class="stat-number">
                    <?= $totalQuizzes ?>
                </div>

                <p class="mb-0 text-muted">
                    Quizzes
                </p>

            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">

                <div class="stat-number">
                    <?= $totalSubs ?>
                </div>

                <p class="mb-0 text-muted">
                    Submissions
                </p>

            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">

                <div class="stat-number">
                    <?= $overallAvg === null
                        ? 'N/A'
                        : $overallAvg . '%' ?>
                </div>

                <p class="mb-0 text-muted">
                    Average Score
                </p>

            </div>
        </div>

    </div>

    <h3 class="section-title">
        Your Quizzes
    </h3>

    <div class="card-box mb-5">

        <div class="table-responsive">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Submissions</th>
                        <th>Average Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($totalQuizzes === 0 && !$dbError): ?>

                    <tr>
                        <td colspan="5" class="text-muted">

                            No quizzes yet.

                            <a href="quiz_upload.php">
                                Upload one or import the existing quizzes.
                            </a>

                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($r['title']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $r['class_name'] ?? ''
                                ) ?>
                            </td>

                            <td>
                                <?= (int) $r['submissions'] ?>
                            </td>

                            <td>
                                <?php if ((int) $r['submissions'] > 0): ?>

                                    <?= (int) $r['average_score'] ?>%

                                <?php else: ?>

                                    —

                                <?php endif; ?>
                            </td>

                            <td>

                                <a
                                    href="../student/quiz_take.php?quiz_id=<?= (int) $r['quiz_id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                    target="_blank"
                                >
                                    Preview
                                </a>

                                <a
                                    href="quiz_results.php?quiz_id=<?= (int) $r['quiz_id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Results
                                </a>

                                <a
                                    href="quiz_edit.php?quiz_id=<?= (int) $r['quiz_id'] ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Edit
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>
