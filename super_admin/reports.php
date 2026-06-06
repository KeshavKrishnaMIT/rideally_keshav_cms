<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_SUPER_ADMIN);

// Posts by status
$byStatus = $conn->query("SELECT status, COUNT(*) AS total FROM posts GROUP BY status");
$statusData = [];
while ($r = $byStatus->fetch_assoc()) $statusData[$r['status']] = $r['total'];

// Posts by category
$byCat = $conn->query("SELECT c.category_name, COUNT(p.id) AS total
    FROM categories c LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id ORDER BY total DESC LIMIT 10");

// Posts by author
$byAuthor = $conn->query("SELECT u.name, COUNT(p.id) AS total
    FROM users u LEFT JOIN posts p ON p.author_id = u.id
    WHERE u.role='author'
    GROUP BY u.id ORDER BY total DESC LIMIT 10");

// Users by role
$byRole = $conn->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role");
$roleData = [];
while ($r = $byRole->fetch_assoc()) $roleData[$r['role']] = $r['total'];

// Comments by status
$cmtStatus = $conn->query("SELECT status, COUNT(*) AS total FROM comments GROUP BY status");
$cmtData = [];
while ($r = $cmtStatus->fetch_assoc()) $cmtData[$r['status']] = $r['total'];

// Monthly posts (last 6 months)
$monthly = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS month, COUNT(*) AS total
    FROM posts
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY created_at ASC
");
$months = []; $monthCounts = [];
while ($r = $monthly->fetch_assoc()) { $months[] = $r['month']; $monthCounts[] = $r['total']; }

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-bar-chart me-2 text-primary"></i>Reports</h1>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <?php
    $allStatuses = [POST_DRAFT, POST_PENDING, POST_APPROVED, POST_REJECTED];
    $icons = ['draft'=>'bi-pencil','pending'=>'bi-hourglass','approved'=>'bi-check-circle','rejected'=>'bi-x-circle'];
    $colors = ['draft'=>'blue','pending'=>'yellow','approved'=>'green','rejected'=>'red'];
    foreach ($allStatuses as $s):
    ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon <?= $colors[$s] ?>"><i class="bi <?= $icons[$s] ?>"></i></div>
            <div>
                <div class="stat-value"><?= $statusData[$s] ?? 0 ?></div>
                <div class="stat-label"><?= ucfirst($s) ?> Posts</div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Posts by Category -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-tags me-2"></i>Posts by Category</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Category</th><th>Posts</th></tr></thead>
                    <tbody>
                    <?php while ($r = $byCat->fetch_assoc()): ?>
                    <tr>
                        <td><?= sanitize($r['category_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= $r['total'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Posts by Author -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Posts by Author</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Author</th><th>Posts</th></tr></thead>
                    <tbody>
                    <?php while ($r = $byAuthor->fetch_assoc()): ?>
                    <tr>
                        <td><?= sanitize($r['name']) ?></td>
                        <td><span class="badge bg-secondary"><?= $r['total'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Users by Role -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people me-2"></i>Users by Role</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Role</th><th>Count</th></tr></thead>
                    <tbody>
                    <?php foreach ($roleData as $role => $count): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= sanitize($role) ?></span></td>
                        <td><?= $count ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comments by Status -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-chat-dots me-2"></i>Comments by Status</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Status</th><th>Count</th></tr></thead>
                    <tbody>
                    <?php foreach ([COMMENT_PENDING, COMMENT_APPROVED, COMMENT_REJECTED] as $s): ?>
                    <tr>
                        <td><span class="badge badge-<?= $s ?>"><?= ucfirst($s) ?></span></td>
                        <td><?= $cmtData[$s] ?? 0 ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Posts -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Monthly Posts (6 Months)</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Month</th><th>Posts</th></tr></thead>
                    <tbody>
                    <?php foreach ($months as $i => $month): ?>
                    <tr><td><?= sanitize($month) ?></td><td><span class="badge bg-secondary"><?= $monthCounts[$i] ?></span></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>