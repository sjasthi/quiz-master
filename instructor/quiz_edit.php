<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : (int) ($_POST['quiz_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $quizId > 0) {
    // Delete
    if (!empty($_POST['delete'])) {
        try {
            qm_delete_quiz($pdo, $quizId);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Quiz deleted.'];
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Delete failed: ' . $e->getMessage()];
            header('Location: quiz_edit.php?quiz_id=' . $quizId);
            exit;
        }
    }

    // Update
    $attempts = trim((string) ($_POST['attempts_allowed'] ?? ''));
    try {
        qm_update_quiz($pdo, $quizId, [
            'title'                => trim((string) ($_POST['title'] ?? '')) ?: 'Untitled Quiz',
            'class_name'           => trim((string) ($_POST['class_name'] ?? '')) ?: 'Python 101',
            'total_points'         => (int) ($_POST['total_points'] ?? 100) ?: 100,
            'attempts_allowed'     => $attempts === '' ? null : (int) $attempts,
            'resubmission_allowed' => isset($_POST['resubmission_allowed']),
            'due_date'             => $_POST['due_date'] ?? null,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Changes saved.'];
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Save failed: ' . $e->getMessage()];
    }
    header('Location: quiz_edit.php?quiz_id=' . $quizId);
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - Quiz Master</title>
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
    <div class="hero">
        <h1>Edit Quiz</h1>
        <p class="mb-0">Update the quiz details below.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> mt-4"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if (!$quiz): ?>
        <div class="alert alert-warning mt-4">
            That quiz could not be found. <a href="dashboard.php">Back to dashboard</a>.
        </div>
    <?php else: ?>
        <div class="card-box form-card mt-4 mb-5">
            <form action="quiz_edit.php" method="post">
                <input type="hidden" name="quiz_id" value="<?= (int) $quiz['quiz_id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Quiz Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($quiz['title']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Class Name</label>
                    <input type="text" name="class_name" class="form-control" value="<?= htmlspecialchars($quiz['class_name'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Points</label>
                        <input type="number" name="total_points" class="form-control" value="<?= (int) $quiz['total_points'] ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Attempts Allowed <span class="text-muted">(blank = unlimited)</span></label>
                        <input type="number" name="attempts_allowed" class="form-control"
                               value="<?= $quiz['attempts_allowed'] === null ? '' : (int) $quiz['attempts_allowed'] ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date <span class="text-muted">(blank = no due date)</span></label>
                    <input type="datetime-local" name="due_date" class="form-control"
                           value="<?= htmlspecialchars(qm_due_date_input_value($quiz)) ?>">
                    <div class="hint">After this time the quiz closes and students can't submit.</div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="resubmission_allowed" class="form-check-input" id="resubmission"
                        <?= !empty($quiz['resubmission_allowed']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="resubmission">Allow resubmission</label>
                </div>

                <button type="submit" class="btn btn-main btn-lg">Save Changes</button>
                <button type="submit" name="delete" value="1" class="btn btn-outline-danger btn-lg"
                        onclick="return confirm('Delete this quiz and all its attempts?');">Delete Quiz</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
