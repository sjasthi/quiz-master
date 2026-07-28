<?php
session_start();

require_once __DIR__ . '/../includes/auth.php';
require_role('student', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;

$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;

if (!$quiz) {
    [$fid] = qm_register_quiz($pdo, [
        'title'                => 'Python Quiz 1',
        'class_name'           => 'Python 101',
        'html_file_path'       => 'quizzes/python/quiz1.html',
        'total_points'         => 100,
        'attempts_allowed'     => null,
        'resubmission_allowed' => true,
    ]);
    $quiz = qm_quiz_by_id($pdo, $fid);
}

$iframeSrc = '../' . ltrim($quiz['html_file_path'], '/');
$pastDue = qm_is_past_due($quiz);
$unpublished = !qm_is_published($quiz);
$blocked = $pastDue || $unpublished;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - Quiz Master</title>
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
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <?php if ($unpublished): ?>
            <p class="mb-0">This quiz is not currently available.</p>
        <?php elseif ($pastDue): ?>
            <p class="mb-0">This quiz closed on <?= htmlspecialchars(date('M j, Y g:i A', strtotime($quiz['due_date']))) ?>.</p>
        <?php else: ?>
            <p class="mb-0">Complete the quiz below. When it finishes, your score appears here so you can record the attempt.</p>
        <?php endif; ?>
    </div>

    <?php if ($blocked): ?>

        <div class="alert alert-warning mt-4">
            <?php if ($unpublished): ?>
                <strong>This quiz is not available.</strong>
                It has not been published by your instructor.
            <?php else: ?>
                <strong>This quiz is closed.</strong>
                The due date (<?= htmlspecialchars(date('M j, Y g:i A', strtotime($quiz['due_date']))) ?>)
                has passed, so it can no longer be taken.
            <?php endif; ?>
        </div>
        <a href="index.php" class="btn btn-main mb-5">Back to Dashboard</a>

    <?php else: ?>

        <iframe
            id="quizFrame"
            class="quiz-frame mt-4"
            style="min-height:700px;"
            src="<?= htmlspecialchars($iframeSrc) ?>"
            title="<?= htmlspecialchars($quiz['title']) ?>">
        </iframe>

        <div id="scoreBox" class="score-box"></div>

        <div id="submitError" class="alert alert-danger mt-3" style="display:none;"></div>

        <div class="mt-4 mb-2">
            <button id="submitButton" class="btn btn-main btn-lg w-100" onclick="recordScore()" disabled>
                Record My Score
            </button>
            <p class="hint text-center">Finish the quiz above to enable this button.</p>
        </div>

        <div class="mb-5"></div>

    <?php endif; ?>

</div>

<?php if (!$blocked): ?>
<script>
    window.QM_QUIZ = {
        id: <?= (int) $quiz['quiz_id'] ?>,
        title: <?= json_encode($quiz['title']) ?>,
        file: <?= json_encode($quiz['html_file_path']) ?>
    };
</script>
<script src="../assets/js/quiz_parent.js"></script>
<?php endif; ?>

</body>
</html>
