<?php
/**
 * Quiz registry helpers (listing / lookup / registration / bulk import).
 *
 * Kept separate from includes/functions.php so the upload + listing features
 * don't collide with the student-submission helpers. Requires a $pdo
 * (include includes/db.php first) and includes/quiz_instrument.php for the
 * score shim used during import/upload.
 */

require_once __DIR__ . '/quiz_instrument.php';

/** All quizzes, newest first. */
function qm_all_quizzes(PDO $pdo): array
{
    return $pdo->query(
        'SELECT * FROM quizzes ORDER BY quiz_id DESC'
    )->fetchAll();
}

/** A single quiz row, or null. */
function qm_quiz_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE quiz_id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Look up a quiz by its stored html_file_path, or null. */
function qm_quiz_by_path(PDO $pdo, string $path): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE html_file_path = :path');
    $stmt->execute(['path' => $path]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * All quizzes plus submission count and average score, for the instructor
 * dashboard / results overview.
 */
function qm_quiz_overview(PDO $pdo): array
{
    return $pdo->query(
        'SELECT q.*,
                COUNT(a.attempt_id)                       AS submissions,
                ROUND(AVG(a.score))                       AS average_score
         FROM quizzes q
         LEFT JOIN quiz_attempts a ON a.quiz_id = q.quiz_id
         GROUP BY q.quiz_id
         ORDER BY q.quiz_id DESC'
    )->fetchAll();
}

/** Turn "7_5_list_comprehension_playbook_and_quiz.html" into a readable title. */
function qm_pretty_title(string $filename): string
{
    $name = preg_replace('/\.html?$/i', '', basename($filename));
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));
    return ucwords($name);
}

/**
 * Register a quiz if its html_file_path isn't already known.
 * $meta keys: title, class_name, html_file_path, total_points,
 * attempts_allowed (int|null), resubmission_allowed (bool).
 * Returns [quiz_id, created(bool)].
 */
function qm_register_quiz(PDO $pdo, array $meta): array
{
    $existing = qm_quiz_by_path($pdo, $meta['html_file_path']);
    if ($existing) {
        return [(int) $existing['quiz_id'], false];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO quizzes
            (title, class_name, html_file_path, total_points, attempts_allowed, resubmission_allowed, due_date)
         VALUES (:title, :class_name, :path, :total_points, :attempts_allowed, :resubmission_allowed, :due_date)'
    );
    $stmt->execute([
        'title'                => $meta['title'],
        'class_name'           => $meta['class_name'] ?? 'Python 101',
        'path'                 => $meta['html_file_path'],
        'total_points'         => $meta['total_points'] ?? 100,
        'attempts_allowed'     => $meta['attempts_allowed'] ?? null,
        'resubmission_allowed' => !empty($meta['resubmission_allowed']) ? 1 : 0,
        'due_date'             => qm_normalize_datetime($meta['due_date'] ?? null),
    ]);

    return [(int) $pdo->lastInsertId(), true];
}

/** Update an existing quiz's metadata (used by the edit form). */
function qm_update_quiz(PDO $pdo, int $id, array $meta): void
{
    $stmt = $pdo->prepare(
        'UPDATE quizzes SET
            title = :title,
            class_name = :class_name,
            total_points = :total_points,
            attempts_allowed = :attempts_allowed,
            resubmission_allowed = :resubmission_allowed,
            due_date = :due_date
         WHERE quiz_id = :id'
    );
    $stmt->execute([
        'title'                => $meta['title'],
        'class_name'           => $meta['class_name'] ?? 'Python 101',
        'total_points'         => $meta['total_points'] ?? 100,
        'attempts_allowed'     => $meta['attempts_allowed'] ?? null,
        'resubmission_allowed' => !empty($meta['resubmission_allowed']) ? 1 : 0,
        'due_date'             => qm_normalize_datetime($meta['due_date'] ?? null),
        'id'                   => $id,
    ]);
}

/** Delete a quiz and its attempts/answers (used by the edit form). */
function qm_delete_quiz(PDO $pdo, int $id): void
{
    $pdo->prepare(
        'DELETE sa FROM student_answers sa
         JOIN quiz_attempts a ON a.attempt_id = sa.attempt_id
         WHERE a.quiz_id = :id'
    )->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM quiz_attempts WHERE quiz_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM quizzes WHERE quiz_id = :id')->execute(['id' => $id]);
}

/**
 * Normalize an <input type="datetime-local"> value ("2026-07-20T15:30") to a
 * MySQL DATETIME ("2026-07-20 15:30:00"), or null if empty.
 */
function qm_normalize_datetime($value): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }
    $ts = strtotime($value);
    return $ts === false ? null : date('Y-m-d H:i:s', $ts);
}

/** True if the quiz has a due date that has already passed. */
function qm_is_past_due(array $quiz): bool
{
    return !empty($quiz['due_date']) && strtotime($quiz['due_date']) < time();
}

/** A datetime-local input value ("2026-07-20T15:30") for a stored due_date, or ''. */
function qm_due_date_input_value(?array $quiz): string
{
    if (!$quiz || empty($quiz['due_date'])) {
        return '';
    }
    return date('Y-m-d\TH:i', strtotime($quiz['due_date']));
}

/**
 * Scan a folder of quiz HTML files: instrument each (idempotent) and register
 * any that aren't in the database yet.
 *
 * @param string $absDir      absolute path to the folder to scan
 * @param string $relPrefix   path stored in the DB, e.g. "quizzes/python"
 * @return array  summary counts + lists
 */
function qm_import_folder(PDO $pdo, string $absDir, string $relPrefix): array
{
    $absDir = rtrim($absDir, '/\\');
    $relPrefix = trim($relPrefix, '/');
    $files = glob($absDir . '/*.html') ?: [];

    $summary = ['registered' => [], 'already' => [], 'instrumented' => 0];

    foreach ($files as $file) {
        // Instrument in place (idempotent — skips files that already report).
        $html = file_get_contents($file);
        [$new, $status] = qm_instrument_html($html);
        if ($status === 'instrumented' && $new !== $html) {
            file_put_contents($file, $new);
            $summary['instrumented']++;
        }

        $relPath = $relPrefix . '/' . basename($file);
        [$id, $created] = qm_register_quiz($pdo, [
            'title'                => qm_pretty_title($file),
            'class_name'           => 'Python 101',
            'html_file_path'       => $relPath,
            'total_points'         => 100,
            'attempts_allowed'     => null,   // unlimited for now
            'resubmission_allowed' => true,
        ]);

        if ($created) {
            $summary['registered'][] = basename($file);
        } else {
            $summary['already'][] = basename($file);
        }
    }

    return $summary;
}
