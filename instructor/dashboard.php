<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quizmaster.css?v=1">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <div>
            <a href="../student/index.php" class="btn btn-outline-secondary btn-sm">Student View</a>
            <span class="ms-2">Instructor Dashboard</span>
        </div>
    </div>
</div>

<div class="container">

    <div class="hero d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>Welcome back, Instructor</h1>
            <p class="mb-0">Manage your quizzes and review student results.</p>
        </div>
        <a href="quiz_upload.php" class="btn btn-light btn-lg mt-3 mt-md-0">+ Upload New Quiz</a>
    </div>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number">1</div>
                <p class="mb-0 text-muted">Published Quiz</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number" id="totalSubmissions">0</div>
                <p class="mb-0 text-muted">Submissions</p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card-box">
                <div class="stat-number" id="averageScore">N/A</div>
                <p class="mb-0 text-muted">Average Score</p>
            </div>
        </div>
    </div>

    <h3 class="section-title">Your Quizzes</h3>

    <div class="card-box mb-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Submissions</th>
                    <th>Average Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Python Quiz 1</td>
                    <td>Python 101</td>
                    <td id="rowSubmissions">0</td>
                    <td id="rowAverage">N/A</td>
                    <td>
                        <a href="quiz_results.php" class="btn btn-sm btn-outline-secondary">Results</a>
                        <a href="quiz_edit.php" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    // For this demo the instructor view reads the same localStorage attempts the
    // student writes, so taking the quiz as a student shows up here right away.
    const attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

    if (attempts.length > 0) {
        const total = attempts.length;
        const average = Math.round(
            attempts.reduce((sum, a) => sum + Number(a.score), 0) / total
        );

        document.getElementById("totalSubmissions").textContent = total;
        document.getElementById("averageScore").textContent = average + "%";
        document.getElementById("rowSubmissions").textContent = total;
        document.getElementById("rowAverage").textContent = average + "%";
    }
</script>

</body>
</html>
