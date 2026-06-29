<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Edit Quiz</h1>
    <p>Update the quiz details below.</p>

    <form class="mt-4" action="#" method="post">

        <div class="mb-3">
            <label class="form-label">Quiz Title</label>
            <input type="text" name="title" class="form-control" value="Python Quiz 1">
        </div>

        <div class="mb-3">
            <label class="form-label">Class Name</label>
            <input type="text" name="class_name" class="form-control" value="Python 101">
        </div>

        <div class="mb-3">
            <label class="form-label">Total Points</label>
            <input type="number" name="total_points" class="form-control" value="100">
        </div>

        <div class="mb-3">
            <label class="form-label">Attempts Allowed</label>
            <input type="number" name="attempts_allowed" class="form-control" value="1">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="resubmission_allowed" class="form-check-input" id="resubmission" checked>
            <label class="form-check-label" for="resubmission">Allow resubmission</label>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="submit" name="delete" value="1" class="btn btn-outline-danger">Delete Quiz</button>
    </form>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</div>
</body>
</html>
