<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Student Dashboard</h1>
    <p>Welcome to Quiz Master. Choose an option below.</p>

    <div class="list-group mt-4">
        <a href="quiz_take.php" class="list-group-item list-group-item-action">
            Take Quiz
        </a>

        <a href="quiz_results.php" class="list-group-item list-group-item-action">
            View Quiz Results
        </a>

        <a href="quiz_history.php" class="list-group-item list-group-item-action">
            View Quiz History
        </a>
    </div>

</div>
</body>
</html>