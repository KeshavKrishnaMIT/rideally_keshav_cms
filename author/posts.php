<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_AUTHOR);

$user = currentUser();
$uid  = (int)$user['id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {

    $id = (int)$_POST['id'];

    $chk = $conn->prepare("
        SELECT id, image, status
        FROM posts
        WHERE id = ? AND author_id = ?
    ");
    $chk->bind_param('ii', $id, $uid);
    $chk->execute();

    $row = $chk->get_result()->fetch_assoc();

    $chk->close();

    if (!$row) {

        $error = 'Post not found.';

    } elseif (!in_array($row['status'], [POST_DRAFT, POST_REJECTED], true)) {

        $error = 'You can only delete draft or rejected posts.';

    } else {

        if (!empty($row['image']) && file_exists(UPLOAD_DIR . $row['image'])) {
            unlink(UPLOAD_DIR . $row['image']);
        }

        $stmt = $conn->prepare("
            DELETE FROM posts
            WHERE id = ?
        ");

        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $success = 'Post deleted.';
        } else {
            $error = 'Failed.';
        }

        $stmt->close();
    }
}

$filter = $_GET['status'] ?? '';

$where  = ["p.author_id = ?"];
$params = [$uid];
$types  = 'i';

if ($filter !== '') {
    $where[] = "p.status = ?";
    $params[] = $filter;
    $types .= 's';
}

$sql = "
    SELECT
        p.id,
        p.title,
        p.status,
        p.created_at,
        p.image,
        c.category_name
    FROM posts p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$posts = $stmt->get_result();

$statuses = [
    ''               => 'All',
    POST_DRAFT       => 'Draft',
    POST_PENDING     => 'Pending',
    POST_APPROVED    => 'Approved',
    POST_REJECTED    => 'Rejected'
];

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>My Posts
    </h1>

    <a href="<?= BASE_URL ?>author/create_post.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Post
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

<form method="GET" class="mb-3 d-flex gap-2">

    <select name="status" class="form-select" style="max-width:180px">

        <?php foreach ($statuses as $value => $label): ?>

            <option value="<?= $value ?>" <?= $filter === $value ? 'selected' : '' ?>>
                <?= $label ?>
            </option>

        <?php endforeach; ?>

    </select>

    <button class="btn btn-outline-primary">
        <i class="bi bi-search me-1"></i>Filter
    </button>

    <?php if ($filter): ?>
        <a href="<?= BASE_URL ?>author/posts.php" class="btn btn-outline-secondary">
            Clear
        </a>
    <?php endif; ?>

</form>

<div class="card">
    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php $i = 1; ?>

                <?php while ($p = $posts->fetch_assoc()): ?>

                    <tr>

                        <td class="text-muted small">
                            <?= $i++ ?>
                        </td>

                        <td>

                            <?php if (!empty($p['image'])): ?>

                                <img
                                    src="<?= UPLOAD_URL . sanitize($p['image']) ?>"
                                    class="post-thumb"
                                    alt="">

                            <?php else: ?>

                                <div
                                    class="post-thumb d-flex align-items-center justify-content-center"
                                    style="background:var(--surface2)">
                                    <i class="bi bi-image text-muted"></i>
                                </div>

                            <?php endif; ?>

                        </td>

                        <td style="max-width:200px">
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

                            <?php if (in_array($p['status'], [POST_DRAFT, POST_REJECTED], true)): ?>

                                <a
                                    href="<?= BASE_URL ?>author/edit_post.php?id=<?= $p['id'] ?>"
                                    class="btn btn-sm btn-outline-primary me-1">

                                    <i class="bi bi-pencil"></i>

                                </a>

                                <form method="POST" class="d-inline">

                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        data-confirm="Delete this post?">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="text-muted small">—</span>

                            <?php endif; ?>

                            <?php if ($p['status'] === POST_REJECTED): ?>

                                <a
                                    href="<?= BASE_URL ?>author/feedback.php?id=<?= $p['id'] ?>"
                                    class="btn btn-sm btn-outline-warning ms-1">

                                    <i class="bi bi-chat-text"></i>

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

<?php
$stmt->close();
include dirname(__DIR__) . '/includes/footer.php';
?>