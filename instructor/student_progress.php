<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
   require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';

$students = [];
$selectedStudent = null;
$quizProgress = [];
$dbError = null;

try {
    $studentStmt = $pdo->query(
        "SELECT
            user_id,
            name,
            email
         FROM users
         WHERE role = 'student'
         ORDER BY name ASC"
    );

    $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$selectedStudentId = filter_input(
    INPUT_GET,
    'student_id',
    FILTER_VALIDATE_INT
);

if ($selectedStudentId && !$dbError) {
    try {
        $selectedStudentStmt = $pdo->prepare(
            "SELECT
                user_id,
                name,
                email
             FROM users
             WHERE user_id = :student_id
               AND role = 'student'
             LIMIT 1"
        );

        $selectedStudentStmt->execute([
            ':student_id' => $selectedStudentId
        ]);

        $selectedStudent = $selectedStudentStmt->fetch(
            PDO::FETCH_ASSOC
        );

        if ($selectedStudent) {
            $progressStmt = $pdo->prepare(
                "SELECT
                    q.quiz_id,
                    q.title,
                    q.class_name,
                    MAX(qa.score) AS highest_score,
                    COUNT(qa.attempt_id) AS attempt_count
                 FROM quizzes q
                 LEFT JOIN quiz_attempts qa
                    ON qa.quiz_id = q.quiz_id
                    AND qa.student_id = :student_id
                 GROUP BY
                    q.quiz_id,
                    q.title,
                    q.class_name
                 ORDER BY
                    q.quiz_id ASC"
            );

            $progressStmt->execute([
                ':student_id' => $selectedStudentId
            ]);

            $quizProgress = $progressStmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        }
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
    }
}

$completedCount = 0;

foreach ($quizProgress as $quiz) {
    if ((int) $quiz['attempt_count'] > 0) {
        $completedCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Progress - Quiz Master</title>

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
                href="dashboard.php"
                class="btn btn-outline-secondary btn-sm"
            >
                Dashboard
            </a>

            <span class="ms-2">
                Student Progress
            </span>
        </div>

    </div>
</div>

<div class="container">

    <div class="hero d-flex justify-content-between align-items-center flex-wrap">

        <div>
            <h1>Student Progress</h1>

            <p class="mb-0">
                Select a student to view their quiz scores and attempts.
            </p>
        </div>

        <a
            href="dashboard.php"
            class="btn btn-outline-light btn-lg mt-3 mt-md-0"
        >
            Back to Dashboard
        </a>

    </div>

    <?php if ($dbError): ?>

        <div class="alert alert-danger mt-4">
            Database not reachable:
            <?= htmlspecialchars($dbError) ?>
        </div>

    <?php endif; ?>

    <h3 class="section-title">
        Choose a Student
    </h3>

    <div class="card-box mb-4">

        <?php if (count($students) === 0 && !$dbError): ?>

            <p class="text-muted mb-0">
                There are no students in the database.
            </p>

        <?php else: ?>

            <form
                method="GET"
                action="student_progress.php"
            >

                <div class="row align-items-end g-3">

                    <div class="col-md-9">

                        <label
                            for="student_id"
                            class="form-label fw-semibold"
                        >
                            Student
                        </label>

                        <select
                            name="student_id"
                            id="student_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select a student
                            </option>

                            <?php foreach ($students as $student): ?>

                                <option
                                    value="<?= (int) $student['user_id'] ?>"
                                    <?= $selectedStudentId === (int) $student['user_id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= htmlspecialchars($student['name']) ?>
                                    — <?= htmlspecialchars($student['email']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >
                            View Progress
                        </button>

                    </div>

                </div>

            </form>

        <?php endif; ?>

    </div>

    <?php if ($selectedStudent): ?>

        <div class="row mb-4">

            <div class="col-md-6 mb-3">

                <div class="card-box h-100">

                    <p class="text-muted mb-1">
                        Student
                    </p>

                    <h3 class="mb-1">
                        <?= htmlspecialchars($selectedStudent['name']) ?>
                    </h3>

                    <p class="mb-0 text-muted">
                        <?= htmlspecialchars($selectedStudent['email']) ?>
                    </p>

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <div class="card-box h-100">

                    <div class="stat-number">
                        <?= $completedCount ?>
                    </div>

                    <p class="mb-0 text-muted">
                        Quizzes Completed
                    </p>

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <div class="card-box h-100">

                    <div class="stat-number">
                        <?= count($quizProgress) ?>
                    </div>

                    <p class="mb-0 text-muted">
                        Total Quizzes
                    </p>

                </div>

            </div>

        </div>

        <h3 class="section-title">
            Quiz Progress
        </h3>

        <div class="card-box mb-5">

            <div class="table-responsive">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Class</th>
                            <th>Highest Score</th>
                            <th>Attempts</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($quizProgress) === 0): ?>

                        <tr>
                            <td
                                colspan="5"
                                class="text-muted"
                            >
                                There are no quizzes in the database.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($quizProgress as $quiz): ?>

                            <?php
                            $attemptCount = (int) $quiz['attempt_count'];
                            $completed = $attemptCount > 0;
                            ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($quiz['title']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $quiz['class_name'] ?? ''
                                    ) ?>
                                </td>

                                <td>

                                    <?php if ($completed): ?>

                                        <?= number_format(
                                            (float) $quiz['highest_score'],
                                            0
                                        ) ?>%

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= $attemptCount ?>
                                </td>

                                <td>

                                    <?php if ($completed): ?>

                                        <span class="badge bg-success">
                                            Completed
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Not Taken
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php elseif (!$selectedStudentId && !$dbError): ?>

        <div class="card-box mb-5">

            <p class="text-muted mb-0">
                Choose a student above to view their quiz progress.
            </p>

        </div>

    <?php elseif ($selectedStudentId && !$selectedStudent): ?>

        <div class="alert alert-warning">
            The selected student could not be found.
        </div>

    <?php endif; ?>

</div>

</body>
</html>
