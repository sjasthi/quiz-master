<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz - Quiz Master</title>
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
        <h1>Edit Quiz</h1>
        <p class="mb-0">Update the quiz details below.</p>
    </div>

    <div class="card-box form-card mt-4 mb-5">
        <form action="#" method="post">

            <div class="mb-3">
                <label class="form-label">Quiz Title</label>
                <input type="text" name="title" class="form-control" value="Python Quiz 1">
            </div>

            <div class="mb-3">
                <label class="form-label">Class Name</label>
                <input type="text" name="class_name" class="form-control" value="Python 101">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Points</label>
                    <input type="number" name="total_points" class="form-control" value="100">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Attempts Allowed</label>
                    <input type="number" name="attempts_allowed" class="form-control" value="1">
                </div>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" name="resubmission_allowed" class="form-check-input" id="resubmission" checked>
                <label class="form-check-label" for="resubmission">Allow resubmission</label>
            </div>

            <button type="submit" class="btn btn-main btn-lg">Save Changes</button>
            <button type="submit" name="delete" value="1" class="btn btn-outline-danger btn-lg">Delete Quiz</button>
        </form>
    </div>

</div>

</body>
</html>
