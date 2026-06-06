<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_SUPER_ADMIN);

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT
        p.*,
        u.name AS author,
        c.category_name,
        e.name AS editor_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users e ON e.id = p.reviewed_by
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header('Location: ' . BASE_URL . 'super_admin/posts.php');
    exit;
}

$commentStmt = $conn->prepare("
    SELECT
        cm.*,
        u.name AS commenter
    FROM comments cm
    LEFT JOIN users u ON u.id = cm.user_id
    WHERE cm.post_id = ?
    ORDER BY cm.created_at DESC
");
$commentStmt->bind_param('i', $id);
$commentStmt->execute();
$comments = $commentStmt->get_result();

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-eye me-2 text-primary"></i>View Post
    </h1>

    <a href="<?= BASE_URL ?>super_admin/posts.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <?php if ($post['image']): ?>
                    <img
                        src="<?= UPLOAD_URL . sanitize($post['image']) ?>"
                        class="img-fluid rounded mb-4"
                        style="max-height:320px;width:100%;object-fit:cover"
                        alt="">
                <?php endif; ?>

                <h2 class="mb-3"><?= sanitize($post['title']) ?></h2>

                <div class="text-muted small mb-4">
                    By <strong><?= sanitize($post['author'] ?? 'Unknown') ?></strong>
                    &bull;
                    <?= sanitize($post['category_name'] ?? 'Uncategorized') ?>
                    &bull;
                    <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?>
                </div>

                <div style="line-height:1.8;color:var(--text)">
                    <?= nl2br(sanitize($post['content'])) ?>
                </div>

            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-chat-dots me-2"></i>Comments
            </div>

            <div class="card-body p-0">

                <?php if ($comments->num_rows === 0): ?>

                    <div class="p-4 text-muted text-center">
                        No comments yet.
                    </div>

                <?php else: ?>

                    <ul class="list-group list-group-flush">

                        <?php while ($cm = $comments->fetch_assoc()): ?>

                            <li class="list-group-item" style="background:transparent;border-color:var(--border)">
                                <div class="d-flex justify-content-between align-items-start">

                                    <div>
                                        <strong class="small">
                                            <?= sanitize($cm['commenter'] ?? 'User') ?>
                                        </strong>

                                        <span class="text-muted small ms-2">
                                            <?= date('d M Y', strtotime($cm['created_at'])) ?>
                                        </span>

                                        <p class="mb-0 mt-1 small">
                                            <?= sanitize($cm['comment']) ?>
                                        </p>
                                    </div>

                                    <span class="badge badge-<?= $cm['status'] ?>">
                                        <?= ucfirst($cm['status']) ?>
                                    </span>

                                </div>
                            </li>

                        <?php endwhile; ?>

                    </ul>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="col-lg-4">

        <div class="card">
            <div class="card-header">
                Post Details
            </div>

            <div class="card-body">

                <table class="table table-sm mb-0">

                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge badge-<?= $post['status'] ?>">
                                <?= ucfirst($post['status']) ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td class="text-muted">Author</td>
                        <td><?= sanitize($post['author'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Category</td>
                        <td><?= sanitize($post['category_name'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Created</td>
                        <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Updated</td>
                        <td><?= date('d M Y', strtotime($post['updated_at'])) ?></td>
                    </tr>

                    <?php if ($post['reviewed_by']): ?>

                        <tr>
                            <td class="text-muted">Reviewed By</td>
                            <td><?= sanitize($post['editor_name'] ?? 'N/A') ?></td>
                        </tr>

                        <tr>
                            <td class="text-muted">Reviewed At</td>
                            <td><?= date('d M Y', strtotime($post['reviewed_at'])) ?></td>
                        </tr>

                    <?php endif; ?>

                </table>

                <?php if ($post['editor_feedback']): ?>

                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Editor Feedback:</strong><br>
                        <?= sanitize($post['editor_feedback']) ?>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>

</div>

<?php
$commentStmt->close();
include dirname(__DIR__) . '/includes/footer.php';
?>