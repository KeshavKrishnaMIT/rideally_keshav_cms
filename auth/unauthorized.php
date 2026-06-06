<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized — RideAlly CMS</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="login-wrapper text-center">
    <div class="login-card">
        <div style="font-size:3rem; color:var(--danger); margin-bottom:1rem;">
            <i class="bi bi-shield-x"></i>
        </div>

        <h2 class="mb-2" style="font-family:'Syne',sans-serif;">Access Denied</h2>

        <p class="text-muted mb-4">
            You do not have permission to view this page.
        </p>

        <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-outline-primary">
                Go to Login
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary">
                Go to Login
            </a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>