<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz History - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quizmaster.css?v=1">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <a href="index.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Quiz History</h1>
        <p class="mb-0">Every quiz attempt you have made.</p>
    </div>

    <div class="card-box mt-4 mb-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Correct</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="historyTable">
                <tr>
                    <td colspan="6" class="text-muted">No quiz attempts yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    const attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

    if (attempts.length > 0) {
        let rows = "";

        attempts.forEach(function (attempt) {
            rows += `
                <tr>
                    <td>${attempt.quiz}</td>
                    <td>${attempt.attempt}</td>
                    <td>${attempt.score}%</td>
                    <td>${attempt.correct}/${attempt.total}</td>
                    <td>${attempt.date}</td>
                    <td><span class="badge bg-success">${attempt.status}</span></td>
                </tr>
            `;
        });

        document.getElementById("historyTable").innerHTML = rows;
    }
</script>

</body>
</html>
