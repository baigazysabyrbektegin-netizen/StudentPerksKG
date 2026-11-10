<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        $redirect = $_SERVER['REQUEST_URI'] ?? 'saved.php';
        header('Location: login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if ((current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

function redirect_after_login(): string
{
    $redirect = (string) ($_GET['redirect'] ?? $_POST['redirect'] ?? 'saved.php');
    if ($redirect === '' || str_starts_with($redirect, '//') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $redirect)) {
        return 'saved.php';
    }

    return ltrim($redirect, '/');
}
