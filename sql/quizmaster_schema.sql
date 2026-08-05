CREATE DATABASE IF NOT EXISTS quiz_master;
USE quiz_master;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);

CREATE TABLE quizzes (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    class_name VARCHAR(100),
    html_file_path VARCHAR(255),
    total_points INT,
    attempts_allowed INT DEFAULT 1,
    resubmission_allowed BOOLEAN DEFAULT FALSE,
    due_date DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT NOT NULL,
    score INT DEFAULT 0,
    correct_answers INT NULL,
    total_questions INT NULL,
    status VARCHAR(30) DEFAULT 'Submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id)
);

CREATE TABLE student_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_number INT NOT NULL,
    student_answer TEXT,
    correct_answer TEXT,
    points_earned INT DEFAULT 0,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id)
);

CREATE TABLE score_overrides (
    override_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    instructor_id INT NOT NULL,
    original_score INT NOT NULL,
    override_score INT NOT NULL,
    note TEXT NOT NULL,
    overridden_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id),
    FOREIGN KEY (instructor_id) REFERENCES users(user_id)
);
