<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_SUPER_ADMIN);

// Stats
$stats = [];

$r = $conn->query("SELECT COUNT(*) AS c FROM users"); $stats['users'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts"); $stats['posts'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='approved'"); $stats['approved'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='pending'"); $stats['pending'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM categories"); $stats['categories'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM comments"); $stats['comments'] = $r->fetch_assoc()['c'];

// Recent posts
$recent = $conn->query("
    SELECT p.id, p.title, p.status, p.created_at, u.name AS author
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    ORDER BY p.created_at DESC LIMIT 8
");

// Recent users
$newUsers = $conn->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 6");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Super Admin Dashboard</h1>
    <span class="text-muted small">Welcome back, <?= sanitize($user['name']) ?></span>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['users'] ?></div>
                <div class="stat-label">Users</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['posts'] ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['approved'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-tags-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['categories'] ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-chat-dots-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['comments'] ?></div>
                <div class="stat-label">Comments</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Posts -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2"></i>Recent Posts</span>
                <a href="<?= BASE_URL ?>super_admin/posts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Title</th><th>Author</th><th>Status</th><th>Date</th>
                        </tr></thead>
                        <tbody>
                        <?php while ($p = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>super_admin/posts.php?view=<?= $p['id'] ?>" class="text-decoration-none text-truncate d-inline-block" style="max-width:220px"><?= sanitize($p['title']) ?></a></td>
                            <td class="text-muted small"><?= sanitize($p['author'] ?? 'N/A') ?></td>
                            <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>New Users</span>
                <a href="<?= BASE_URL ?>super_admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                <?php while ($u = $newUsers->fetch_assoc()): ?>
                <li class="list-group-item" style="background:transparent;border-color:var(--border)">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-circle"><?= strtoupper(substr($u['name'],0,1)) ?></span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-600 small text-truncate"><?= sanitize($u['name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= sanitize($u['role']) ?></div>
                        </div>
                        <span class="badge <?= $u['status']==='active' ? 'badge-approved' : 'badge-rejected' ?>"><?= $u['status'] ?></span>
                    </div>
                </li>
                <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>