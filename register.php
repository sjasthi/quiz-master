<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . (current_user_role() === 'instructor' ? 'instructor/dashboard.php' : 'student/index.php'));
    exit;
}

$error = null;
$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role  = $_POST['role'] ?? 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in every field.';
    } elseif (!in_array($role, ['student', 'instructor'], true)) {
        $error = 'Please choose a valid account type.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($role === 'instructor' && ($_POST['instructor_code'] ?? '') !== INSTRUCTOR_SIGNUP_CODE) {
        $error = 'That instructor code is incorrect.';
    } else {
        try {
            $check = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
            $check->execute(['email' => $email]);

            if ($check->fetchColumn()) {
                $error = 'An account with that email already exists. Try logging in instead.';
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO users (name, email, password, role)
                     VALUES (:name, :email, :password, :role)'
                );
                $insert->execute([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role'     => $role,
                ]);

                $_SESSION['user_id']   = (int) $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                header('Location: ' . ($role === 'instructor' ? 'instructor/dashboard.php' : 'student/index.php'));
                exit;
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Quiz Master</title>
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
        <h1>Create an Account</h1>
        <p class="mb-0">Sign up as a student or instructor.</p>
    </div>

    <div class="row justify-content-center mt-4 mb-5">
        <div class="col-md-5">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card-box form-card">
                <form action="register.php" method="post">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($name) ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="hint">At least 6 characters.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">I am a...</label>
                        <select name="role" id="roleSelect" class="form-select">
                            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="instructor" <?= $role === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                        </select>
                    </div>

                    <div class="mb-4" id="instructorCodeField" style="display:none;">
                        <label class="form-label">Instructor Code</label>
                        <input type="text" name="instructor_code" class="form-control"
                               placeholder="Provided by your program">
                        <div class="hint">Ask your program coordinator for this code.</div>
                    </div>

                    <button type="submit" class="btn btn-main btn-lg w-100">Sign Up</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Already have an account? <a href="login.php">Log in</a>
                </p>
            </div>

        </div>
    </div>

</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const instructorCodeField = document.getElementById('instructorCodeField');

    function toggleInstructorCode() {
        instructorCodeField.style.display = roleSelect.value === 'instructor' ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', toggleInstructorCode);
    toggleInstructorCode();
</script>

</body>
</html>
