<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_USER);

$user = currentUser();

$uid     = (int)$user['id'];
$id      = (int)($_GET['id'] ?? 0);
$success = '';
$error   = '';
$stmt = $conn->prepare("
    SELECT
        p.*,
        u.name AS author,
        c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
    AND p.status = 'approved'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header('Location: ' . BASE_URL . 'user/posts.php');
    exit;
}

if (isset($_GET['comment']) && $_GET['comment'] === 'success') {
    $success = 'Comment posted successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $comment = trim($_POST['comment'] ?? '');

    if ($comment === '') {

        $error = 'Comment cannot be empty.';

    } else {

        $stmt = $conn->prepare("
            INSERT INTO comments
            (
                post_id,
                user_id,
                comment,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'approved'
            )
        ");

        $stmt->bind_param(
            'iis',
            $id,
            $uid,
            $comment
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                'Location: ' .
                BASE_URL .
                'user/view_post.php?id=' .
                $id .
                '&comment=success'
            );

            exit;

        } else {

            $error = 'Failed to post comment.';
        }

        $stmt->close();
    }
}

$stmt = $conn->prepare("
    SELECT
        cm.*,
        u.name AS commenter
    FROM comments cm
    LEFT JOIN users u
        ON u.id = cm.user_id
    WHERE cm.post_id = ?
    AND cm.status = 'approved'
    ORDER BY cm.created_at DESC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$comments = $stmt->get_result();

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title" style="font-size:1.3rem">
        <i class="bi bi-newspaper me-2 text-primary"></i>
        <?= sanitize($post['title']) ?>
    </h1>

    <a href="<?= BASE_URL ?>user/posts.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-body">

                <?php if (!empty($post['image'])): ?>
                    <img
                        src="<?= UPLOAD_URL . sanitize($post['image']) ?>"
                        class="img-fluid rounded mb-4"
                        style="max-height:360px;width:100%;object-fit:cover"
                        alt=""
                    >
                <?php endif; ?>

                <h2 class="mb-2"><?= sanitize($post['title']) ?></h2>

                <div class="d-flex flex-wrap gap-3 text-muted small mb-4">

                    <span>
                        <i class="bi bi-person me-1"></i>
                        <?= sanitize($post['author'] ?? 'Unknown') ?>
                    </span>

                    <?php if (!empty($post['category_name'])): ?>
                        <span>
                            <i class="bi bi-tag me-1"></i>
                            <?= sanitize($post['category_name']) ?>
                        </span>
                    <?php endif; ?>

                    <span>
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?>
                    </span>

                </div>

                <div style="line-height:1.9;font-size:1rem">
                    <?= nl2br(sanitize($post['content'])) ?>
                </div>

            </div>
        </div>

        <div class="card">

            <div class="card-header">
                <i class="bi bi-chat-dots me-2"></i>
                Comments (<?= $comments->num_rows ?>)
            </div>

            <div class="card-body">

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

                <form method="POST" class="mb-4">

                    <label class="form-label">
                        Leave a Comment
                    </label>

                    <textarea
                        name="comment"
                        class="form-control mb-2"
                        rows="3"
                        placeholder="Share your thoughts..."
                        required
                    ></textarea>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>
                        Post Comment
                    </button>

                </form>

                <?php if ($comments->num_rows === 0): ?>

                    <p class="text-muted text-center py-3">
                        No approved comments yet. Be the first!
                    </p>

                <?php else: ?>

                    <?php while ($cm = $comments->fetch_assoc()): ?>

                        <div
                            class="d-flex gap-3 mb-3 pb-3 border-bottom"
                            style="border-color:var(--border)!important"
                        >

                            <span class="avatar-circle flex-shrink-0">
                                <?= strtoupper(substr($cm['commenter'] ?? 'U', 0, 1)) ?>
                            </span>

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

                        </div>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card">

            <div class="card-header">
                About this Post
            </div>

            <div class="card-body">

                <table class="table table-sm mb-0">

                    <tr>
                        <td class="text-muted">Author</td>
                        <td><?= sanitize($post['author'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Category</td>
                        <td><?= sanitize($post['category_name'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Published</td>
                        <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                    </tr>

                    <tr>
                        <td class="text-muted">Updated</td>
                        <td><?= date('d M Y', strtotime($post['updated_at'])) ?></td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>