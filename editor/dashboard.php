<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_EDITOR);

$user = currentUser();

if (!$user) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$uid = (int)$user['id'];

$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='pending'");
$pending = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='approved'");
$approved = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='rejected'");
$rejected = $r->fetch_assoc()['c'];

$r = $conn->prepare("SELECT COUNT(*) AS c FROM posts WHERE reviewed_by=?");
$r->bind_param('i', $uid);
$r->execute();
$myReviews = $r->get_result()->fetch_assoc()['c'];
$r->close();

$pendingPosts = $conn->query("
    SELECT
        p.id,
        p.title,
        p.created_at,
        u.name AS author,
        c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status='pending'
    ORDER BY p.created_at ASC
    LIMIT 8
");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-speedometer2 me-2 text-primary"></i>Editor Dashboard
    </h1>

    <span class="text-muted small">
        Welcome, <?= sanitize($user['name']) ?>
    </span>
</div>

<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value"><?= $pending ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $approved ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $rejected ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $myReviews ?></div>
                <div class="stat-label">My Reviews</div>
            </div>
        </div>
    </div>

</div>

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-hourglass me-2"></i>Posts Pending Review
        </span>

        <a href="<?= BASE_URL ?>editor/posts.php"
           class="btn btn-sm btn-outline-primary">
            View All
        </a>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($pendingPosts->num_rows > 0): ?>

                    <?php while ($p = $pendingPosts->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= sanitize($p['title']) ?>
                        </td>

                        <td class="text-muted small">
                            <?= sanitize($p['author'] ?? 'N/A') ?>
                        </td>

                        <td class="text-muted small">
                            <?= sanitize($p['category_name'] ?? '—') ?>
                        </td>

                        <td class="text-muted small">
                            <?= date('d M Y', strtotime($p['created_at'])) ?>
                        </td>

                        <td>
                            <a href="<?= BASE_URL ?>editor/review_post.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i>Review
                            </a>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No pending posts found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>