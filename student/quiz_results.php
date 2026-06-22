<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Quiz Results</h1>
    <p>Your most recent quiz result is shown below.</p>

    <div class="card mt-4">
        <div class="card-body">
            <h5>Python Quiz 1</h5>
            <p>Score: 85 / 100</p>
            <p>Status: Submitted</p>
        </div>
    </div>

    <br>
    <a href="index.php">Back to Dashboard</a>

</div>
</body>
</html>