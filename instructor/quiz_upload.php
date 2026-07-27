<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
   require_role('instructor', '../login.php');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$rootDir    = dirname(__DIR__);
$uploadsAbs = $rootDir . '/quizzes/uploads';

/** Store a one-time message, then redirect (Post/Redirect/Get). */
function qm_flash_redirect(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    header('Location: quiz_upload.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'upload';

    // ---- Bulk import: register every quiz already in quizzes/python ----
    if ($action === 'import') {
        try {
            $sum = qm_import_folder($pdo, $rootDir . '/quizzes/python', 'quizzes/python');
            $msg = sprintf(
                'Imported %d new quiz(zes); %d already registered. Instrumented %d file(s) for scoring.',
                count($sum['registered']), count($sum['already']), $sum['instrumented']
            );
            qm_flash_redirect('success', $msg);
        } catch (Throwable $e) {
            qm_flash_redirect('danger', 'Import failed: ' . $e->getMessage());
        }
    }

    // ---- Single upload ----
    $file = $_FILES['html_file'] ?? null;

    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        qm_flash_redirect('danger', 'Please choose an HTML quiz file to upload.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        qm_flash_redirect('danger', 'The file did not upload correctly. Please try again.');
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        qm_flash_redirect('danger', 'That file is larger than the 3 MB limit.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['html', 'htm'], true)) {
        qm_flash_redirect('danger', 'Only .html files can be uploaded.');
    }

    // Safe, unique destination filename.
    $base = preg_replace('/\.html?$/i', '', basename($file['name']));
    $base = preg_replace('/[^A-Za-z0-9._-]/', '_', $base);
    if ($base === '') {
        $base = 'quiz';
    }
    $name = $base . '.html';
    $n = 1;
    while (file_exists($uploadsAbs . '/' . $name)) {
        $name = $base . '_' . (++$n) . '.html';
    }

    if (!is_dir($uploadsAbs) && !mkdir($uploadsAbs, 0775, true) && !is_dir($uploadsAbs)) {
        qm_flash_redirect('danger', 'Could not create the uploads folder on the server.');
    }

    // Instrument the quiz so it reports its score, then save it.
    $html = file_get_contents($file['tmp_name']);
    [$html] = qm_instrument_html($html);
    if (file_put_contents($uploadsAbs . '/' . $name, $html) === false) {
        qm_flash_redirect('danger', 'Could not save the uploaded file.');
    }

    $title = trim((string) ($_POST['title'] ?? '')) ?: qm_pretty_title($name);
    $attempts = trim((string) ($_POST['attempts_allowed'] ?? ''));

    try {
        [$id, $created] = qm_register_quiz($pdo, [
            'title'                => $title,
            'class_name'           => trim((string) ($_POST['class_name'] ?? '')) ?: 'Python 101',
            'html_file_path'       => 'quizzes/uploads/' . $name,
            'total_points'         => (int) ($_POST['total_points'] ?? 100) ?: 100,
            'attempts_allowed'     => $attempts === '' ? null : (int) $attempts,
            'resubmission_allowed' => isset($_POST['resubmission_allowed']),
            'due_date'             => $_POST['due_date'] ?? null,
        ]);
        qm_flash_redirect('success', 'Uploaded and registered "' . htmlspecialchars($title) . '". It is now takeable and will show up in the quiz list.');
    } catch (Throwable $e) {
        qm_flash_redirect('danger', 'Saved the file but could not register it: ' . $e->getMessage());
    }
}

// ---- GET: render ----
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$quizzes = [];
$dbError = null;
try {
    $quizzes = qm_all_quizzes($pdo);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Quiz - Quiz Master</title>
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
        <h1>Upload New Quiz</h1>
        <p class="mb-0">Add a quiz HTML file. It is automatically instrumented to report its score,
            registered so students can take it, and listed below.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> mt-4"><?= $flash['msg'] ?></div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-lg-7 mb-4">
            <div class="card-box form-card h-100">
                <h4 class="mb-3">Upload a single quiz</h4>
                <form action="quiz_upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">

                    <div class="mb-3">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Python Data Types Quiz">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="class_name" class="form-control" placeholder="Python 101">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quiz HTML File</label>
                        <input type="file" name="html_file" class="form-control" accept=".html,.htm" required>
                        <div class="hint">Any quiz format works — the score reporter is injected on upload.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Points</label>
                            <input type="number" name="total_points" class="form-control" placeholder="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Attempts Allowed</label>
                            <input type="number" name="attempts_allowed" class="form-control" placeholder="unlimited">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date <span class="text-muted">(optional)</span></label>
                        <input type="datetime-local" name="due_date" class="form-control">
                        <div class="hint">After this time the quiz closes and students can't submit.</div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="resubmission_allowed" class="form-check-input" id="resubmission" checked>
                        <label class="form-check-label" for="resubmission">Allow resubmission</label>
                    </div>

                    <button type="submit" class="btn btn-main btn-lg">Upload Quiz</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card-box h-100">
                <h4 class="mb-3">Import existing quizzes</h4>
                <p class="text-muted">
                    Register every quiz already in <code>quizzes/python/</code> at once, so all of them
                    show up in the UI. Safe to run repeatedly — quizzes already registered are skipped.
                </p>
                <form action="quiz_upload.php" method="post">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Import quizzes from folder</button>
                </form>
            </div>
        </div>
    </div>

    <h3 class="section-title">Registered Quizzes
        <?php if (!$dbError): ?><span class="text-muted">(<?= count($quizzes) ?>)</span><?php endif; ?>
    </h3>

    <div class="card-box mb-5">
        <?php if ($dbError): ?>
            <p class="text-danger mb-0">Database not reachable: <?= htmlspecialchars($dbError) ?></p>
        <?php elseif (count($quizzes) === 0): ?>
            <p class="text-muted mb-0">No quizzes registered yet. Upload one above, or use “Import quizzes from folder”.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['title']) ?></td>
                            <td><?= htmlspecialchars($q['class_name'] ?? '') ?></td>
                            <td><code><?= htmlspecialchars($q['html_file_path'] ?? '') ?></code></td>
                            <td>
                                <a href="quiz_preview.php?quiz_id=<?= (int) $q['quiz_id'] ?>"
                                   class="btn btn-sm btn-outline-secondary" target="_blank">Preview</a>
                                <a href="quiz_results.php?quiz_id=<?= (int) $q['quiz_id'] ?>"
                                   class="btn btn-sm btn-outline-secondary">Results</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
