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
            (title, class_name, html_file_path, total_points, attempts_allowed, resubmission_allowed)
         VALUES (:title, :class_name, :path, :total_points, :attempts_allowed, :resubmission_allowed)'
    );
    $stmt->execute([
        'title'                => $meta['title'],
        'class_name'           => $meta['class_name'] ?? 'Python 101',
        'path'                 => $meta['html_file_path'],
        'total_points'         => $meta['total_points'] ?? 100,
        'attempts_allowed'     => $meta['attempts_allowed'] ?? null,
        'resubmission_allowed' => !empty($meta['resubmission_allowed']) ? 1 : 0,
    ]);

    return [(int) $pdo->lastInsertId(), true];
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
