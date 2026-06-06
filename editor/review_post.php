<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_EDITOR);

$user = currentUser();

if (!$user) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$id  = (int)($_GET['id'] ?? 0);
$uid = (int)$user['id'];
$success = '';
$error = '';

$stmt = $conn->prepare("
    SELECT
        p.*,
        u.name AS author,
        c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header('Location: ' . BASE_URL . 'editor/posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($post['status'] !== POST_PENDING) {
        $error = 'This post has already been reviewed.';
    } else {

        $action   = $_POST['action'] ?? '';
        $feedback = trim($_POST['editor_feedback'] ?? '');
        $now      = date('Y-m-d H:i:s');

        if ($action === 'approve') {

            $stmt = $conn->prepare("
                UPDATE posts
                SET
                    status='approved',
                    reviewed_by=?,
                    reviewed_at=?,
                    editor_feedback=?
                WHERE id=?
            ");

            $stmt->bind_param('issi', $uid, $now, $feedback, $id);

            if ($stmt->execute()) {
                $success = 'Post approved and published.';
            } else {
                $error = 'Failed to update.';
            }

            $stmt->close();

        } elseif ($action === 'reject') {

            if ($feedback === '') {

                $error = 'Please provide feedback when rejecting a post.';

            } else {

                $stmt = $conn->prepare("
                    UPDATE posts
                    SET
                        status='rejected',
                        reviewed_by=?,
                        reviewed_at=?,
                        editor_feedback=?
                    WHERE id=?
                ");

                $stmt->bind_param('issi', $uid, $now, $feedback, $id);

                if ($stmt->execute()) {
                    $success = 'Post rejected with feedback.';
                } else {
                    $error = 'Failed to update.';
                }

                $stmt->close();
            }
        }

        $stmt = $conn->prepare("
            SELECT
                p.*,
                u.name AS author,
                c.category_name
            FROM posts p
            LEFT JOIN users u ON u.id = p.author_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Review Post
    </h1>

    <a href="<?= BASE_URL ?>editor/posts.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<?php if ($success): ?>
<div class="alert alert-success" data-auto-dismiss>
    <?= sanitize($success) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger" data-auto-dismiss>
    <?= sanitize($error) ?>
</div>
<?php endif; ?>

<div class="row g-4">

    <div class="col-lg-8">

        <div class="card">
            <div class="card-body">

                <?php if (!empty($post['image'])): ?>
                    <img
                        src="<?= UPLOAD_URL . sanitize($post['image']) ?>"
                        class="img-fluid rounded mb-4"
                        style="max-height:300px;width:100%;object-fit:cover"
                        alt="">
                <?php endif; ?>

                <h2 class="mb-2"><?= sanitize($post['title']) ?></h2>

                <div class="text-muted small mb-4">
                    By <strong><?= sanitize($post['author'] ?? 'Unknown') ?></strong>
                    &bull;
                    <?= sanitize($post['category_name'] ?? 'Uncategorized') ?>
                    &bull;
                    <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?>
                </div>

                <div style="line-height:1.8">
                    <?= nl2br(sanitize($post['content'])) ?>
                </div>

            </div>
        </div>

    </div>

    <div class="col-lg-4">

        <div class="card">

            <div class="card-header">
                Post Info
            </div>

            <div class="card-body">

                <table class="table table-sm mb-3">

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
                        <td class="text-muted">Submitted</td>
                        <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                    </tr>

                </table>

                <?php if ($post['status'] === POST_PENDING): ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">
                                Editor Feedback
                                <small class="text-muted">(required for rejection)</small>
                            </label>

                            <textarea
                                name="editor_feedback"
                                class="form-control"
                                rows="4"
                                placeholder="Write your feedback..."><?= sanitize($post['editor_feedback'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                name="action"
                                value="approve"
                                class="btn btn-success">

                                <i class="bi bi-check-circle me-2"></i>
                                Approve & Publish

                            </button>

                            <button
                                type="submit"
                                name="action"
                                value="reject"
                                class="btn btn-danger">

                                <i class="bi bi-x-circle me-2"></i>
                                Reject Post

                            </button>

                        </div>

                    </form>

                <?php else: ?>

                    <?php if (!empty($post['editor_feedback'])): ?>

                        <div class="alert alert-warning mb-0">
                            <strong>Editor Feedback:</strong><br>
                            <?= sanitize($post['editor_feedback']) ?>
                        </div>

                    <?php else: ?>

                        <p class="text-muted small mb-0">
                            This post has already been <?= sanitize($post['status']) ?>.
                        </p>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>