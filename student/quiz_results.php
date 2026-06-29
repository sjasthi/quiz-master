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
        <a href="index.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Quiz Results</h1>
        <p class="mb-0">Here is your most recent quiz score.</p>
    </div>

    <div class="card-box mt-4 mb-5">
        <h3>Python Quiz 1</h3>

        <div id="resultsContent">
            <p class="text-muted">No quiz result found yet.</p>
            <a href="quiz_take.php" class="btn btn-main">Take Quiz</a>
        </div>
    </div>

</div>

<script>
    const attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];
    const latestAttempt = attempts[attempts.length - 1];

    if (latestAttempt) {
        document.getElementById("resultsContent").innerHTML = `
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number">${latestAttempt.score}%</div>
                        <p class="mb-0 text-muted">Final Grade</p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number">${latestAttempt.correct}/${latestAttempt.total}</div>
                        <p class="mb-0 text-muted">Correct Answers</p>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card-box">
                        <div class="stat-number">Attempt ${latestAttempt.attempt}</div>
                        <p class="mb-0 text-muted">Latest Attempt</p>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mt-3">
                Your latest Python Quiz 1 score was saved for this demo.
            </div>

            <a href="quiz_take.php" class="btn btn-outline-primary mt-3">Retake Quiz</a>
            <a href="index.php" class="btn btn-main mt-3">Return to Dashboard</a>
        `;
    }
</script>

</body>
</html>