<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Quiz - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Upload New Quiz</h1>
    <p>Fill in the quiz details and upload the quiz HTML file.</p>

    <form class="mt-4" action="#" method="post" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Quiz Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Python Quiz 1">
        </div>

        <div class="mb-3">
            <label class="form-label">Class Name</label>
            <input type="text" name="class_name" class="form-control" placeholder="e.g. Python 101">
        </div>

        <div class="mb-3">
            <label class="form-label">Quiz HTML File</label>
            <input type="file" name="html_file" class="form-control" accept=".html">
        </div>

        <div class="mb-3">
            <label class="form-label">Total Points</label>
            <input type="number" name="total_points" class="form-control" placeholder="100">
        </div>

        <div class="mb-3">
            <label class="form-label">Attempts Allowed</label>
            <input type="number" name="attempts_allowed" class="form-control" placeholder="1">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="resubmission_allowed" class="form-check-input" id="resubmission">
            <label class="form-check-label" for="resubmission">Allow resubmission</label>
        </div>

        <button type="submit" class="btn btn-primary">Upload Quiz</button>
    </form>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</div>
</body>
</html>
