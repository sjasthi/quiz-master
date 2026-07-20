<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . (current_user_role() === 'instructor' ? 'instructor/dashboard.php' : 'student/index.php'));
    exit;
}
?>
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

    <div class="row justify-content-center mb-5">
        <div class="col-md-5 mb-4">
            <div class="role-card text-center">
                <div class="role-emoji">🔑</div>
                <h4>Already have an account?</h4>
                <p class="text-muted mb-3">Log in as a student or instructor.</p>
                <a href="login.php" class="btn btn-main">Log In</a>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="role-card text-center">
                <div class="role-emoji">📝</div>
                <h4>New here?</h4>
                <p class="text-muted mb-3">Create a student or instructor account.</p>
                <a href="register.php" class="btn btn-main">Sign Up</a>
            </div>
        </div>
    </div>

</div>

</body>
</html>
