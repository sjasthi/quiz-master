<?php
/**
 * CSV export of a quiz's results (instructor only).
 * Usage: api/export_csv.php?quiz_id=N
 * Downloads a .csv with each submission, applying any score overrides.
 */
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor', '../login.php');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/quiz_repo.php';

$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;
$quiz = $quizId ? qm_quiz_by_id($pdo, $quizId) : null;

if (!$quiz) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Quiz not found.';
    exit;
}

$submissions = qm_quiz_submissions($pdo, $quizId);
$overrides   = qm_overrides_for_quiz($pdo, $quizId);

$slug = preg_replace('/[^A-Za-z0-9]+/', '_', trim($quiz['title'])) ?: 'quiz';
$slug = trim($slug, '_');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $slug . '_results.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, [
    'Student Name', 'Email', 'Attempt', 'Score (%)', 'Correct', 'Total',
    'Adjusted', 'Override Note', 'Status', 'Submitted At',
]);

foreach ($submissions as $s) {
    $aid = (int) $s['attempt_id'];
    $ov  = $overrides[$aid] ?? null;
    $score = $ov ? (int) $ov['override_score'] : (int) $s['score'];

    fputcsv($out, [
        $s['student_name'],
        $s['student_email'] ?? '',
        (int) $s['attempt_number'],
        $score,
        $s['correct_answers'] ?? '',
        $s['total_questions'] ?? '',
        $ov ? 'Yes' : 'No',
        $ov ? $ov['note'] : '',
        $s['status'],
        $s['submitted_at'],
    ]);
}

fclose($out);
