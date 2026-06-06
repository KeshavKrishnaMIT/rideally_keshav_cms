<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_AUTHOR);

$user = currentUser();
$uid  = (int)$user['id'];

$id      = (int)($_GET['id'] ?? 0);
$success = $error = '';
$stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND author_id=?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post || !in_array($post['status'], [POST_DRAFT, POST_REJECTED])) {
    header('Location: ' . BASE_URL . 'author/posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $category = (int)($_POST['category_id'] ?? 0);
    $category = $category ?: null;

    $action = $_POST['submit_action'] ?? 'draft';
    $status = ($action === 'submit') ? POST_PENDING : POST_DRAFT;

    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {

        $imageName = $post['image'];

        if (!empty($_FILES['image']['name'])) {

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $error = 'Invalid image type.';
            } elseif ($_FILES['image']['size'] > (3 * 1024 * 1024)) {
                $error = 'Image must be under 3MB.';
            } else {

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0775, true);
                }

                $newImage = uniqid('img_', true) . '.' . $ext;

                if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $newImage)) {

                    if (
                        !empty($imageName) &&
                        file_exists(UPLOAD_DIR . $imageName)
                    ) {
                        unlink(UPLOAD_DIR . $imageName);
                    }

                    $imageName = $newImage;

                } else {
                    $error = 'Image upload failed.';
                }
            }
        }

        if ($error === '') {

            $stmt = $conn->prepare("
                UPDATE posts
                SET
                    title = ?,
                    content = ?,
                    image = ?,
                    category_id = ?,
                    status = ?,
                    reviewed_by = NULL,
                    reviewed_at = NULL,
                    editor_feedback = NULL
                WHERE id = ?
                AND author_id = ?
            ");

            $categoryId = $category;

            $stmt->bind_param(
                'sssisii',
                $title,
                $content,
                $imageName,
                $categoryId,
                $status,
                $id,
                $uid
            );

            if ($stmt->execute()) {
                $success = ($status === POST_PENDING)
                    ? 'Post submitted for review.'
                    : 'Draft saved.';
            } else {
                $error = 'Failed to update post.';
            }

            $stmt->close();

            $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $post = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
}

$cats = $conn->query("
    SELECT id, category_name
    FROM categories
    ORDER BY category_name ASC
");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-pencil me-2 text-primary"></i>Edit Post
    </h1>

    <a href="<?= BASE_URL ?>author/posts.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>My Posts
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

<?php if (!empty($post['editor_feedback'])): ?>
<div class="alert alert-warning">
    <i class="bi bi-chat-text me-2"></i>
    <strong>Editor Feedback:</strong>
    <?= sanitize($post['editor_feedback']) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <form method="POST" enctype="multipart/form-data">

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">
                        Post Title <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control form-control-lg"
                        required
                        value="<?= sanitize($post['title']) ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>

                    <select name="category_id" class="form-select">
                        <option value="">— Select Category —</option>

                        <?php while ($c = $cats->fetch_assoc()): ?>
                        <option
                            value="<?= $c['id'] ?>"
                            <?= ($post['category_id'] == $c['id']) ? 'selected' : '' ?>
                        >
                            <?= sanitize($c['category_name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">

                    <label class="form-label">Featured Image</label>

                    <?php if (!empty($post['image'])): ?>
                    <div class="mb-2">
                        <img
                            src="<?= UPLOAD_URL . sanitize($post['image']) ?>"
                            alt="Current Image"
                            style="height:80px;object-fit:cover;border-radius:6px"
                        >

                        <small class="text-muted d-block mt-1">
                            Upload a new image to replace this one
                        </small>
                    </div>
                    <?php endif; ?>

                    <input
                        type="file"
                        name="image"
                        id="imageInput"
                        class="form-control"
                        accept="image/*"
                    >

                    <img
                        id="imagePreview"
                        src=""
                        class="mt-2 rounded d-none"
                        style="max-height:120px;object-fit:cover"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Content <span class="text-danger">*</span>
                    </label>

                    <textarea
                        name="content"
                        class="form-control"
                        rows="12"
                        required
                    ><?= sanitize($post['content']) ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap">

                    <button
                        type="submit"
                        name="submit_action"
                        value="draft"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-floppy me-2"></i>Save Draft
                    </button>

                    <button
                        type="submit"
                        name="submit_action"
                        value="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-send me-2"></i>Submit for Review
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>