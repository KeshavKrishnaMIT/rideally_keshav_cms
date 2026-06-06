<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_ADMIN);

$r = $conn->query("SELECT COUNT(*) AS c FROM users"); $tu = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts"); $tp = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='approved'"); $ap = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='pending'"); $pp = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM categories"); $tc = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) AS c FROM comments"); $cm = $r->fetch_assoc()['c'];

$recent = $conn->query("SELECT p.id, p.title, p.status, p.created_at, u.name AS author
    FROM posts p LEFT JOIN users u ON u.id=p.author_id ORDER BY p.created_at DESC LIMIT 8");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h1>
    <span class="text-muted small">Welcome, <?= sanitize($user['name']) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-people-fill"></i></div><div><div class="stat-value"><?= $tu ?></div><div class="stat-label">Users</div></div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon orange"><i class="bi bi-file-earmark-text-fill"></i></div><div><div class="stat-value"><?= $tp ?></div><div class="stat-label">Posts</div></div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div><div><div class="stat-value"><?= $ap ?></div><div class="stat-label">Approved</div></div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon yellow"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-value"><?= $pp ?></div><div class="stat-label">Pending</div></div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-tags-fill"></i></div><div><div class="stat-value"><?= $tc ?></div><div class="stat-label">Categories</div></div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-icon orange"><i class="bi bi-chat-dots-fill"></i></div><div><div class="stat-value"><?= $cm ?></div><div class="stat-label">Comments</div></div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text me-2"></i>Recent Posts</span>
        <a href="<?= BASE_URL ?>admin/posts.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php while ($p = $recent->fetch_assoc()): ?>
                <tr>
                    <td><?= sanitize($p['title']) ?></td>
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

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>