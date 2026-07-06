<?php

function get_or_create_demo_student(PDO $pdo): int
{
    if (!empty($_SESSION['student_id'])) {
        return (int) $_SESSION['student_id'];
    }

    $email = 'demo.student@quizmaster.local';

    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $_SESSION['student_id'] = (int) $existingId;
        return (int) $existingId;
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (name, email, password, role)
         VALUES (:name, :email, :password, :role)'
    );
    $insert->execute([
        'name'     => 'Demo Student',
        'email'    => $email,
        'password' => password_hash('demo-password', PASSWORD_DEFAULT),
        'role'     => 'student',
    ]);

    $studentId = (int) $pdo->lastInsertId();
    $_SESSION['student_id'] = $studentId;

    return $studentId;
}

function get_or_create_quiz(PDO $pdo, string $htmlFilePath, string $title): array
{
    $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE html_file_path = :path');
    $stmt->execute(['path' => $htmlFilePath]);
    $quiz = $stmt->fetch();

    if ($quiz) {
        return $quiz;
    }

    $insert = $pdo->prepare(
        'INSERT INTO quizzes
            (title, class_name, html_file_path, total_points, attempts_allowed, resubmission_allowed)
         VALUES (:title, :class_name, :path, :total_points, NULL, TRUE)'
    );
    $insert->execute([
        'title'        => $title,
        'class_name'   => 'Python 101',
        'path'         => $htmlFilePath,
        'total_points' => 100,
    ]);

    $newId = $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE quiz_id = :id');
    $stmt->execute(['id' => $newId]);

    return $stmt->fetch();
}

function get_attempts_for_student(PDO $pdo, int $studentId, ?int $quizId = null): array
{
    if ($quizId !== null) {
        $stmt = $pdo->prepare(
            'SELECT a.*, q.title AS quiz_title
             FROM quiz_attempts a
             JOIN quizzes q ON q.quiz_id = a.quiz_id
             WHERE a.student_id = :student_id AND a.quiz_id = :quiz_id
             ORDER BY a.attempt_number ASC'
        );
        $stmt->execute(['student_id' => $studentId, 'quiz_id' => $quizId]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT a.*, q.title AS quiz_title
             FROM quiz_attempts a
             JOIN quizzes q ON q.quiz_id = a.quiz_id
             WHERE a.student_id = :student_id
             ORDER BY a.submitted_at ASC'
        );
        $stmt->execute(['student_id' => $studentId]);
    }

    return $stmt->fetchAll();
}

function get_answer_summary(PDO $pdo, int $attemptId): array
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total, COALESCE(SUM(points_earned), 0) AS correct
         FROM student_answers
         WHERE attempt_id = :attempt_id'
    );
    $stmt->execute(['attempt_id' => $attemptId]);
    $row = $stmt->fetch();

    return [
        'correct' => (int) ($row['correct'] ?? 0),
        'total'   => (int) ($row['total'] ?? 0),
    ];
}

function get_best_score(PDO $pdo, int $studentId, int $quizId): ?int
{
    $stmt = $pdo->prepare(
        'SELECT MAX(score) FROM quiz_attempts WHERE student_id = :student_id AND quiz_id = :quiz_id'
    );
    $stmt->execute(['student_id' => $studentId, 'quiz_id' => $quizId]);
    $best = $stmt->fetchColumn();

    return $best !== null ? (int) $best : null;
}
