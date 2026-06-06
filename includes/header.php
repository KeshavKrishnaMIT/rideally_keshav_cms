<?php
require_once dirname(__DIR__) . '/includes/auth.php';

requireLogin();

$user = currentUser();
$role = currentRole();

$dashboardUrl = BASE_URL . match($role) {
    ROLE_SUPER_ADMIN => 'super_admin/dashboard.php',
    ROLE_ADMIN       => 'admin/dashboard.php',
    ROLE_EDITOR      => 'editor/dashboard.php',
    ROLE_AUTHOR      => 'author/dashboard.php',
    default          => 'user/dashboard.php',
};

$roleLabel = match($role) {
    ROLE_SUPER_ADMIN => 'Super Admin',
    ROLE_ADMIN       => 'Admin',
    ROLE_EDITOR      => 'Editor',
    ROLE_AUTHOR      => 'Author',
    default          => 'User'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keshav's CMS</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" id="mainNav">
    <div class="container-fluid">

        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $dashboardUrl ?>">
            <div class="brand-icon">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>

            <div>
                <div class="brand-title">
                    Keshav's CMS
                </div>
                <div class="brand-subtitle">
                    Content Management System
                </div>
            </div>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <?php if (hasRole(ROLE_SUPER_ADMIN)): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>super_admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>super_admin/users.php">
                            <i class="bi bi-people me-1"></i>
                            Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>super_admin/categories.php">
                            <i class="bi bi-tags me-1"></i>
                            Categories
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>super_admin/posts.php">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>super_admin/reports.php">
                            <i class="bi bi-bar-chart me-1"></i>
                            Reports
                        </a>
                    </li>

                <?php elseif (hasRole(ROLE_ADMIN)): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>admin/users.php">
                            <i class="bi bi-people me-1"></i>
                            Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>admin/categories.php">
                            <i class="bi bi-tags me-1"></i>
                            Categories
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>admin/posts.php">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>admin/reports.php">
                            <i class="bi bi-bar-chart me-1"></i>
                            Reports
                        </a>
                    </li>

                <?php elseif (hasRole(ROLE_EDITOR)): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>editor/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>editor/posts.php">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Review Posts
                        </a>
                    </li>

                <?php elseif (hasRole(ROLE_AUTHOR)): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>author/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>author/posts.php">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            My Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>author/create_post.php">
                            <i class="bi bi-plus-circle me-1"></i>
                            New Post
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>user/dashboard.php">
                            <i class="bi bi-house-door me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>user/posts.php">
                            <i class="bi bi-newspaper me-1"></i>
                            Posts
                        </a>
                    </li>

                <?php endif; ?>

                <li class="nav-item dropdown ms-lg-4">

                    <a
                        class="nav-link dropdown-toggle profile-link"
                        href="#"
                        data-bs-toggle="dropdown">

                        <span class="avatar-circle">
                            <?= strtoupper(substr($user['name'],0,1)) ?>
                        </span>

                        <div class="profile-meta">
                            <div class="profile-name">
                                <?= sanitize($user['name']) ?>
                            </div>

                            <div class="profile-role">
                                <?= $roleLabel ?>
                            </div>
                        </div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow">

                        <li class="px-3 py-2">
                            <div class="fw-semibold">
                                <?= sanitize($user['name']) ?>
                            </div>

                            <small class="text-muted">
                                <?= sanitize($user['email']) ?>
                            </small>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <span class="dropdown-item-text">
                                <i class="bi bi-person-badge me-2"></i>
                                <?= $roleLabel ?>
                            </span>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a
                                class="dropdown-item text-danger"
                                href="<?= BASE_URL ?>auth/logout.php">

                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>

                    </ul>

                </li>

            </ul>

        </div>
    </div>
</nav>

<div class="page-wrapper">