<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . (current_user_role() === 'instructor' ? 'instructor/dashboard.php' : 'student/index.php'));
    exit;
}

$error = null;

if (isset($_GET['error']) && $_GET['error'] === 'wrong_role') {
    $error = "You're logged in, but that page isn't available for your account type.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = (int) $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                header('Location: ' . ($user['role'] === 'instructor' ? 'instructor/dashboard.php' : 'student/index.php'));
                exit;
            }

            $error = 'Incorrect email or password.';
        } catch (Throwable $e) {
            $error = 'Database not reachable: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In - Quiz Master</title>
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
        <h1>Log In</h1>
        <p class="mb-0">Enter your email and password to continue.</p>
    </div>

    <div class="row justify-content-center mt-4 mb-5">
        <div class="col-md-5">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card-box form-card">
                <form action="login.php" method="post">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-main btn-lg w-100">Log In</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Don't have an account? <a href="register.php">Sign up</a>
                </p>
            </div>

        </div>
    </div>

</div>

</body>
</html>
