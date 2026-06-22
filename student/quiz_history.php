<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz History - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Quiz History</h1>
    <p>Below is your quiz attempt history.</p>

    <table class="table table-bordered mt-4">
        <tr>
            <th>Quiz</th>
            <th>Attempt</th>
            <th>Score</th>
            <th>Status</th>
        </tr>

        <tr>
            <td>Python Quiz 1</td>
            <td>1</td>
            <td>85 / 100</td>
            <td>Submitted</td>
        </tr>
    </table>

    <br>
    <a href="index.php">Back to Dashboard</a>

</div>
</body>
</html>