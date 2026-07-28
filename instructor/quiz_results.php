<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = (int) ($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);

// ---- Handle a score override submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'override') {
    $attemptId     = (int) ($_POST['attempt_id'] ?? 0);
    $originalScore = (int) ($_POST['original_score'] ?? 0);
    $overrideScore = $_POST['override_score'] ?? '';
    $note          = trim((string) ($_POST['note'] ?? ''));

    if ($attemptId <= 0 || !is_numeric($overrideScore)
        || (int) $overrideScore < 0 || (int) $overrideScore > 100 || $note === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'An override needs a score between 0 and 100 and a required note.'];
    } else {
        try {
            qm_add_override($pdo, $attemptId, current_user_id(), $originalScore, (int) $overrideScore, $note);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Score override saved.'];
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Override failed: ' . $e->getMessage()];
        }
    }
    header('Location: quiz_results.php?quiz_id=' . $quizId);
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;
$submissions = $quiz ? qm_quiz_submissions($pdo, $quizId) : [];
$overrides   = $quiz ? qm_overrides_for_quiz($pdo, $quizId) : [];

// Effective score = override if present, else the raw score.
$effective = [];
foreach ($submissions as $s) {
    $aid = (int) $s['attempt_id'];
    $effective[] = isset($overrides[$aid]) ? (int) $overrides[$aid]['override_score'] : (int) $s['score'];
}
$count = count($submissions);
$average = $count > 0 ? (int) round(array_sum($effective) / $count) : null;
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
        <div class="hero d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Quiz Results</h1>
                <p class="mb-0">Student submissions for <strong><?= htmlspecialchars($quiz['title']) ?></strong>.</p>
            </div>
            <?php if ($count > 0): ?>
                <a href="../api/export_csv.php?quiz_id=<?= (int) $quiz['quiz_id'] ?>"
                   class="btn btn-light btn-lg mt-3 mt-md-0">Export CSV</a>
            <?php endif; ?>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> mt-4"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($count === 0): ?>
                            <tr>
                                <td colspan="6" class="text-muted">No submissions yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $s): ?>
                                <?php $aid = (int) $s['attempt_id']; $ov = $overrides[$aid] ?? null; ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_name']) ?></td>
                                    <td><?= (int) $s['attempt_number'] ?></td>
                                    <td>
                                        <?php if ($ov): ?>
                                            <strong><?= (int) $ov['override_score'] ?>%</strong>
                                            <span class="text-muted"><del><?= (int) $s['score'] ?>%</del></span>
                                            <span class="badge bg-warning text-dark ms-1"
                                                  title="<?= htmlspecialchars($ov['note']) ?>">Instructor adjusted</span>
                                        <?php else: ?>
                                            <?= (int) $s['score'] ?>%
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($s['status']) ?></span></td>
                                    <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($s['submitted_at']))) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#overrideModal"
                                                data-attempt="<?= $aid ?>"
                                                data-student="<?= htmlspecialchars($s['student_name'], ENT_QUOTES) ?>"
                                                data-score="<?= $ov ? (int) $ov['override_score'] : (int) $s['score'] ?>">
                                            Override
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Score override modal -->
        <div class="modal fade" id="overrideModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="quiz_results.php">
                        <input type="hidden" name="action" value="override">
                        <input type="hidden" name="quiz_id" value="<?= (int) $quiz['quiz_id'] ?>">
                        <input type="hidden" name="attempt_id" id="ov_attempt">
                        <input type="hidden" name="original_score" id="ov_original">

                        <div class="modal-header">
                            <h5 class="modal-title">Override Score</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">
                                Overriding the score for <strong id="ov_student"></strong>
                                (current: <span id="ov_current"></span>%).
                            </p>
                            <div class="mb-3">
                                <label class="form-label">New Score (%)</label>
                                <input type="number" name="override_score" class="form-control" min="0" max="100" required>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Reason for override <span class="text-danger">(required)</span></label>
                                <textarea name="note" class="form-control" rows="3" required
                                          placeholder="e.g. Re-graded question 4 after review."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-main">Save Override</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const overrideModal = document.getElementById('overrideModal');
    if (overrideModal) {
        overrideModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('ov_attempt').value = btn.getAttribute('data-attempt');
            document.getElementById('ov_original').value = btn.getAttribute('data-score');
            document.getElementById('ov_student').textContent = btn.getAttribute('data-student');
            document.getElementById('ov_current').textContent = btn.getAttribute('data-score');
        });
    }
</script>
</body>
</html>
