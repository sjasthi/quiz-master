<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student.css?v=6">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <div>Student Dashboard</div>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Welcome back, Student</h1>
        <p class="mb-0">Start a quiz, check your score, and track your progress.</p>
    </div>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number">1</div>
                <p class="mb-0 text-muted">Available Quiz</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number" id="completedQuizzes">0</div>
                <p class="mb-0 text-muted">Completed Quiz</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number" id="currentScore">N/A</div>
                <p class="mb-0 text-muted">Current Grade</p>
            </div>
        </div>
    </div>

    <h3 class="section-title">Available Quizzes</h3>

    <div class="row">

        <div class="col-md-6 mb-4">
            <div class="card-box">
                <span class="quiz-tag tag-open">Open</span>
                <h4>Python Quiz 1</h4>
                <p class="text-muted">Basic Python data types and syntax.</p>

                <a href="quiz_take.php" class="btn btn-main w-100">
                    Start Quiz
                </a>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card-box">
                <span class="quiz-tag tag-locked" id="javaStatus">Locked</span>
                <h4>Java Quiz 1</h4>
                <p class="text-muted" id="javaMessage">
                    Complete Python Quiz 1 with at least 75% to unlock this quiz.
                </p>

                <button class="btn btn-secondary w-100" id="javaButton" disabled>
                    Locked
                </button>
            </div>
        </div>

    </div>

    <h3 class="section-title">Quiz Attempts</h3>

    <div class="card-box mb-5">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody id="attemptTable">
                <tr>
                    <td colspan="5" class="text-muted">No quiz attempts yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    const attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

    if (attempts.length > 0) {
        const latestAttempt = attempts[attempts.length - 1];

        document.getElementById("completedQuizzes").textContent = "1";
        document.getElementById("currentScore").textContent = latestAttempt.score + "%";

        let rows = "";

        attempts.forEach(function(attempt) {
            rows += `
                <tr>
                    <td>${attempt.quiz}</td>
                    <td>${attempt.attempt}</td>
                    <td>${attempt.score}%</td>
                    <td>${attempt.date}</td>
                    <td><span class="badge bg-success">${attempt.status}</span></td>
                </tr>
            `;
        });

        document.getElementById("attemptTable").innerHTML = rows;
    }

    const savedScore = localStorage.getItem("pythonQuizScore");

    if (savedScore !== null && Number(savedScore) >= 75) {
        document.getElementById("javaStatus").textContent = "Open";
        document.getElementById("javaStatus").className = "quiz-tag tag-open";

        document.getElementById("javaMessage").textContent =
            "Unlocked because you passed Python Quiz 1 with at least 75%.";

        const javaButton = document.getElementById("javaButton");
        javaButton.disabled = false;
        javaButton.className = "btn btn-main w-100";
        javaButton.textContent = "Start Quiz";
    }
</script>

</body>
</html>