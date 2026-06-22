<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Take Quiz</h1>
    <p>Select and complete your quiz here.</p>

    <div class="card mt-4">
        <div class="card-header">
            Python Quiz 1
        </div>

        <div class="card-body">
            <p>This is where the quiz HTML file will load.</p>

            <iframe src="../quizzes/python/quiz1.html"
            width="100%"
            height="500"></iframe>

            <br><br>

            <a href="quiz_results.php" class="btn btn-primary">Submit Quiz</a>
        </div>
    </div>

    <br>
    <a href="index.php">Back to Dashboard</a>

</div>
</body>
</html>