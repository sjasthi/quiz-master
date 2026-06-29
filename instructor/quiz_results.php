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
    <p>Student submissions for <strong>Python Quiz 1</strong>.</p>

    <div class="row mt-4">
        <div class="col">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Submissions</h6>
                    <p class="fs-4 mb-0">1</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Average Score</h6>
                    <p class="fs-4 mb-0">85 / 100</p>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered mt-4">
        <tr>
            <th>Student</th>
            <th>Attempt</th>
            <th>Score</th>
            <th>Status</th>
            <th>Submitted At</th>
        </tr>

        <tr>
            <td>Jane Student</td>
            <td>1</td>
            <td>85 / 100</td>
            <td>Submitted</td>
            <td>2026-06-22</td>
        </tr>
    </table>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</div>
</body>
</html>
