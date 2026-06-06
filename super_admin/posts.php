<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_SUPER_ADMIN);

$success = $error = '';

// Delete post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    $imgStmt = $conn->prepare("SELECT image FROM posts WHERE id=?");
    $imgStmt->bind_param('i', $id);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();

    if ($row = $imgResult->fetch_assoc()) {
        if (!empty($row['image']) && file_exists(UPLOAD_DIR . $row['image'])) {
            unlink(UPLOAD_DIR . $row['image']);
        }
    }

    $imgStmt->close();

    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    $stmt->bind_param('i', $id);

    $stmt->execute()
        ? $success = 'Post deleted.'
        : $error = 'Delete failed.';

    $stmt->close();
}

$filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filter !== '') {
    $where[] = "p.status=?";
    $params[] = $filter;
    $types .= 's';
}

if ($search !== '') {
    $like = "%$search%";
    $where[] = "(p.title LIKE ? OR u.name LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql = "
    SELECT
        p.id,
        p.title,
        p.status,
        p.created_at,
        p.image,
        u.name AS author,
        c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id = p.author_id
    LEFT JOIN categories c ON c.id = p.category_id
"
. ($where ? ' WHERE ' . implode(' AND ', $where) : '')
. " ORDER BY p.created_at DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $posts = $stmt->get_result();
} else {
    $posts = $conn->query($sql);
}

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
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>All Posts
    </h1>
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

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
    <input
        type="text"
        name="search"
        class="form-control"
        style="max-width:260px"
        placeholder="Search title or author…"
        value="<?= sanitize($search) ?>">

    <select name="status" class="form-select" style="max-width:160px">
        <?php foreach ($statuses as $val => $label): ?>
        <option value="<?= $val ?>" <?= $filter === $val ? 'selected' : '' ?>>
            <?= $label ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-outline-primary">
        <i class="bi bi-search me-1"></i>Filter
    </button>

    <?php if ($filter || $search): ?>
    <a href="<?= BASE_URL ?>super_admin/posts.php" class="btn btn-outline-secondary">
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
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <?php $i = 1; while ($p = $posts->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted small"><?= $i++ ?></td>

                    <td>
                        <?php if ($p['image']): ?>
                            <img src="<?= UPLOAD_URL . sanitize($p['image']) ?>" class="post-thumb" alt="">
                        <?php else: ?>
                            <div class="post-thumb d-flex align-items-center justify-content-center bg-dark text-muted">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td style="max-width:220px">
                        <span class="d-inline-block text-truncate w-100">
                            <?= sanitize($p['title']) ?>
                        </span>
                    </td>

                    <td class="text-muted small">
                        <?= sanitize($p['author'] ?? 'N/A') ?>
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
                        <a href="<?= BASE_URL ?>super_admin/view_post.php?id=<?= $p['id'] ?>"
                           class="btn btn-sm btn-outline-secondary me-1">
                            <i class="bi bi-eye"></i>
                        </a>

                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">

                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-danger"
                                data-confirm="Delete this post permanently?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>