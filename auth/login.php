<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (isLoggedIn()) {
    redirectDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and Password are required.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, name, email, password, role, status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = 'Invalid email or password.';
        } elseif ($user['status'] === USER_BANNED) {
            $error = 'Your account has been banned.';
        } elseif ($user['status'] === USER_INACTIVE) {
            $error = 'Your account is inactive.';
        } elseif ($user['password'] !== $password) {
            $error = 'Invalid email or password.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;

            redirectDashboard();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keshav's CMS - Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<div class="login-wrapper">

    <div class="login-card">

        <div class="login-logo">
            Keshav's CMS
        </div>

        <p class="text-center text-muted mb-4">
            Content Management System
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger" data-auto-dismiss>
                <?= sanitize($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Email Address</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    value="<?= sanitize($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Sign In
            </button>

        </form>

        <div class="text-center mt-4 text-muted small">
            © <?= date('Y') ?> Keshav's CMS
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

</body>
</html>