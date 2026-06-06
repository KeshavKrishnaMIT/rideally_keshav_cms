<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_AUTHOR);

$user = currentUser();
$uid = (int)$user['id'];

$r=$conn->prepare("SELECT COUNT(*) AS c FROM posts WHERE author_id=?");
$r->bind_param('i',$uid);
$r->execute();
$total=$r->get_result()->fetch_assoc()['c'];

$r=$conn->prepare("SELECT COUNT(*) AS c FROM posts WHERE author_id=? AND status='approved'");
$r->bind_param('i',$uid);
$r->execute();
$appr=$r->get_result()->fetch_assoc()['c'];

$r=$conn->prepare("SELECT COUNT(*) AS c FROM posts WHERE author_id=? AND status='pending'");
$r->bind_param('i',$uid);
$r->execute();
$pend=$r->get_result()->fetch_assoc()['c'];

$r=$conn->prepare("SELECT COUNT(*) AS c FROM posts WHERE author_id=? AND status='rejected'");
$r->bind_param('i',$uid);
$r->execute();
$rej=$r->get_result()->fetch_assoc()['c'];

$stmt=$conn->prepare("
    SELECT
        p.id,
        p.title,
        p.status,
        p.created_at,
        c.category_name
    FROM posts p
    LEFT JOIN categories c ON c.id=p.category_id
    WHERE p.author_id=?
    ORDER BY p.created_at DESC
    LIMIT 8
");
$stmt->bind_param('i',$uid);
$stmt->execute();
$posts=$stmt->get_result();

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-speedometer2 me-2 text-primary"></i>
        Author Dashboard
    </h1>

    <a href="<?= BASE_URL ?>author/create_post.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Post
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $appr ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value"><?= $pend ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= $rej ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-file-earmark-text me-2"></i>
            My Recent Posts
        </span>

        <a href="<?= BASE_URL ?>author/posts.php" class="btn btn-sm btn-outline-primary">
            View All
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>
                <?php while($p=$posts->fetch_assoc()): ?>
                <tr>
                    <td style="max-width:220px">
                        <span class="d-inline-block text-truncate w-100">
                            <?= sanitize($p['title']) ?>
                        </span>
                    </td>

                    <td class="text-muted small">
                        <?= sanitize($p['category_name'] ?? '—') ?>
                    </td>

                    <td>
                        <span class="badge badge-<?= $p['status'] ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>

                    <td class="text-muted small">
                        <?= date('d M Y', strtotime($p['created_at'])) ?>
                    </td>

                    <td>
                        <?php if(in_array($p['status'], [POST_DRAFT, POST_REJECTED])): ?>
                            <a href="<?= BASE_URL ?>author/edit_post.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>