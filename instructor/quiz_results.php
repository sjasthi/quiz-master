<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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

    <div class="hero">
        <h1>Quiz Results</h1>
        <p class="mb-0">Student submissions for <strong>Python Quiz 1</strong>.</p>
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="card-box">
                <div class="stat-number" id="submissionCount">0</div>
                <p class="mb-0 text-muted">Submissions</p>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card-box">
                <div class="stat-number" id="averageScore">N/A</div>
                <p class="mb-0 text-muted">Average Score</p>
            </div>
        </div>
    </div>

    <div class="card-box mb-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody id="resultsTable">
                <tr>
                    <td colspan="5" class="text-muted">No submissions yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Reads the same localStorage attempts the student writes for this demo.
    const attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

    if (attempts.length > 0) {
        const total = attempts.length;
        const average = Math.round(
            attempts.reduce((sum, a) => sum + Number(a.score), 0) / total
        );

        document.getElementById("submissionCount").textContent = total;
        document.getElementById("averageScore").textContent = average + "%";

        let rows = "";
        attempts.forEach(function (attempt) {
            rows += `
                <tr>
                    <td>Demo Student</td>
                    <td>${attempt.attempt}</td>
                    <td>${attempt.score}% (${attempt.correct}/${attempt.total})</td>
                    <td><span class="badge bg-success">${attempt.status}</span></td>
                    <td>${attempt.date}</td>
                </tr>
            `;
        });

        document.getElementById("resultsTable").innerHTML = rows;
    }
</script>

</body>
</html>
