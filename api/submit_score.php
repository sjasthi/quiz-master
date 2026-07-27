<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

function reject(int $httpStatus, string $message): void
{
    http_response_code($httpStatus);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if (!is_logged_in() || current_user_role() !== 'student') {
    reject(401, 'You must be logged in as a student to submit a quiz score.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reject(405, 'Only POST requests are accepted.');
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    reject(400, 'Invalid or missing JSON body.');
}

$quizTitle = trim((string) ($data['quizTitle'] ?? ''));
$quizFile  = trim((string) ($data['quizFile'] ?? ''));
$score     = $data['score'] ?? null;
$correct   = $data['correctAnswers'] ?? null;
$total     = $data['totalQuestions'] ?? null;
$answers   = $data['answers'] ?? [];

if ($quizFile === '' || $quizTitle === '') {
    reject(400, 'Missing quiz identifier.');
}

if (!is_numeric($score) || !is_numeric($correct) || !is_numeric($total)) {
    reject(400, 'Score fields must be numeric.');
}

$score   = (int) $score;
$correct = (int) $correct;
$total   = (int) $total;

if ($total <= 0 || $correct < 0 || $correct > $total || $score < 0 || $score > 100) {
    reject(400, 'Score values are out of range.');
}

$expectedScore = (int) round(($correct / $total) * 100);

if ($expectedScore !== $score) {
    reject(400, 'Score does not match the reported correct-answer count.');
}

if (!is_array($answers)) {
    $answers = [];
}

if (count($answers) > 0 && count($answers) !== $total) {
    reject(400, 'Answer list does not match the total question count.');
}

try {
    $studentId = current_user_id();
    $quiz      = get_or_create_quiz($pdo, $quizFile, $quizTitle);

    $quizId              = (int) $quiz['quiz_id'];
    $attemptsAllowed      = $quiz['attempts_allowed'];
    $resubmissionAllowed = (bool) $quiz['resubmission_allowed'];

    // Due-date locking: reject submissions after the quiz has closed.
    if (!empty($quiz['due_date']) && strtotime($quiz['due_date']) < time()) {
        reject(403, 'This quiz is past its due date and is now closed.');
    }

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = :quiz_id AND student_id = :student_id'
    );
    $countStmt->execute(['quiz_id' => $quizId, 'student_id' => $studentId]);
    $priorAttempts = (int) $countStmt->fetchColumn();

    if ($priorAttempts >= 1 && !$resubmissionAllowed) {
        reject(409, 'This quiz does not allow resubmission and you already have a recorded attempt.');
    }

    if ($attemptsAllowed !== null && $priorAttempts >= (int) $attemptsAllowed) {
        reject(409, 'You have used all ' . (int) $attemptsAllowed . ' allowed attempt(s) for this quiz.');
    }

    $attemptNumber = $priorAttempts + 1;

    $pdo->beginTransaction();

    $insertAttempt = $pdo->prepare(
        'INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score, status)
         VALUES (:quiz_id, :student_id, :attempt_number, :score, :status)'
    );
    $insertAttempt->execute([
        'quiz_id'        => $quizId,
        'student_id'     => $studentId,
        'attempt_number' => $attemptNumber,
        'score'          => $score,
        'status'         => 'Submitted',
    ]);

    $attemptId = (int) $pdo->lastInsertId();

    if (count($answers) > 0) {
        $insertAnswer = $pdo->prepare(
            'INSERT INTO student_answers
                (attempt_id, question_number, student_answer, correct_answer, points_earned)
             VALUES (:attempt_id, :question_number, :student_answer, :correct_answer, :points_earned)'
        );

        foreach ($answers as $index => $answer) {
            if (!is_array($answer)) {
                continue;
            }

            $questionNumber = isset($answer['questionNumber'])
                ? (int) $answer['questionNumber']
                : $index + 1;

            $insertAnswer->execute([
                'attempt_id'      => $attemptId,
                'question_number' => $questionNumber,
                'student_answer'  => (string) ($answer['studentAnswer'] ?? ''),
                'correct_answer'  => (string) ($answer['correctAnswer'] ?? ''),
                'points_earned'   => !empty($answer['isCorrect']) ? 1 : 0,
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'attempt_id'     => $attemptId,
        'attempt_number' => $attemptNumber,
        'score'          => $score,
        'correct'        => $correct,
        'total'          => $total,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('submit_score.php error: ' . $e->getMessage());
    reject(500, 'Could not save your submission. Please try again.');
}
