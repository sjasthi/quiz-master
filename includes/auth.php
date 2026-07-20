<?php
/**
 * Lightweight session-based auth for Quiz Master.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function require_login(string $loginPath = 'login.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . $loginPath);
        exit;
    }
}

function require_role(string $role, string $loginPath = 'login.php'): void
{
    require_login($loginPath);

    if (current_user_role() !== $role) {
        header('Location: ' . $loginPath . '?error=wrong_role');
        exit;
    }
}
