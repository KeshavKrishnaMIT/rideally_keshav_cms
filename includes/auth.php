<?php
require_once dirname(__DIR__) . '/config/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function currentRole(): ?string {
    return $_SESSION['user']['role'] ?? null;
}

function hasRole(string ...$roles): bool {
    return in_array(currentRole(), $roles, true);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!hasRole(...$roles)) {
        header('Location: ' . BASE_URL . 'auth/unauthorized.php');
        exit;
    }
}

function redirectDashboard(): void {
    $role = currentRole();
    $map = [
        ROLE_SUPER_ADMIN => BASE_URL . 'super_admin/dashboard.php',
        ROLE_ADMIN       => BASE_URL . 'admin/dashboard.php',
        ROLE_EDITOR      => BASE_URL . 'editor/dashboard.php',
        ROLE_AUTHOR      => BASE_URL . 'author/dashboard.php',
        ROLE_USER        => BASE_URL . 'user/dashboard.php',
    ];
    header('Location: ' . ($map[$role] ?? BASE_URL . 'auth/login.php'));
    exit;
}

function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function generateSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}