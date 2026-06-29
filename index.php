<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/quizmaster.css?v=1">
</head>

<body>

<div class="topbar">
    <div class="container">
        <div class="brand">Quiz Master</div>
    </div>
</div>

<div class="container">

    <div class="hero text-center">
        <h1>Welcome to Quiz Master</h1>
        <p class="mb-0">Take quizzes, track your progress, and manage your class — all in one place.</p>
    </div>

    <h3 class="section-title text-center">Choose how you want to sign in</h3>

    <div class="row justify-content-center mb-5">
        <div class="col-md-5 mb-4">
            <a href="student/index.php" class="text-decoration-none text-dark">
                <div class="role-card">
                    <div class="role-emoji">🎓</div>
                    <h4>I'm a Student</h4>
                    <p class="text-muted mb-3">Take quizzes and see your scores and attempt history.</p>
                    <span class="btn btn-main">Enter Student Dashboard</span>
                </div>
            </a>
        </div>

        <div class="col-md-5 mb-4">
            <a href="instructor/dashboard.php" class="text-decoration-none text-dark">
                <div class="role-card">
                    <div class="role-emoji">🧑‍🏫</div>
                    <h4>I'm an Instructor</h4>
                    <p class="text-muted mb-3">Upload quizzes, edit settings, and review student results.</p>
                    <span class="btn btn-main">Enter Instructor Dashboard</span>
                </div>
            </a>
        </div>
    </div>

</div>

</body>
</html>
