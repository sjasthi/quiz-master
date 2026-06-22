<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">

    <h1>Instructor Dashboard</h1>
    <p>Welcome to Quiz Master. Manage your quizzes and review student results below.</p>

    <a href="quiz_upload.php" class="btn btn-primary mt-2">+ Upload New Quiz</a>

    <h4 class="mt-5">Your Quizzes</h4>
    <table class="table table-bordered mt-3">
        <tr>
            <th>Title</th>
            <th>Class</th>
            <th>Submissions</th>
            <th>Average Score</th>
            <th>Actions</th>
        </tr>

        <tr>
            <td>Python Quiz 1</td>
            <td>Python 101</td>
            <td>1</td>
            <td>85 / 100</td>
            <td>
                <a href="quiz_results.php" class="btn btn-sm btn-outline-secondary">Results</a>
                <a href="quiz_edit.php" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
