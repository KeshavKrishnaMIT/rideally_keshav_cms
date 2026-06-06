<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireRole(ROLE_AUTHOR);

$user = currentUser();
$uid  = (int)$user['id'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $category   = (int)($_POST['category_id'] ?? 0) ?: null;
    $action     = $_POST['submit_action'] ?? 'draft';
    $status     = ($action === 'submit') ? POST_PENDING : POST_DRAFT;

    if (!$title || !$content) {
        $error = 'Title and content are required.';
    } else {
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
            } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                $error = 'Image must be under 3MB.';
            } else {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
                $imageName = uniqid('img_', true) . '.' . $ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $imageName)) {
                    $error = 'Failed to upload image.';
                    $imageName = null;
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO posts (title,content,image,category_id,author_id,status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('sssiss', $title, $content, $imageName, $category, $uid, $status);
            if ($stmt->execute()) {
                $success = $status === POST_PENDING ? 'Post submitted for review.' : 'Post saved as draft.';
            } else {
                $error = 'Failed to save post.';
            }
            $stmt->close();
        }
    }
}

$cats = $conn->query("SELECT id,category_name FROM categories ORDER BY category_name ASC");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Create New Post</h1>
    <a href="<?=BASE_URL?>author/posts.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>My Posts</a>
</div>

<?php if($success):?><div class="alert alert-success" data-auto-dismiss><?=sanitize($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger" data-auto-dismiss><?=sanitize($error)?></div><?php endif;?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Post Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg" required
                        placeholder="Enter a compelling title…" value="<?=sanitize($_POST['title']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">— Select Category —</option>
                        <?php while($c=$cats->fetch_assoc()):?>
                        <option value="<?=$c['id']?>" <?=(isset($_POST['category_id'])&&$_POST['category_id']==$c['id'])?'selected':''?>>
                            <?=sanitize($c['category_name'])?>
                        </option>
                        <?php endwhile;?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Featured Image <small class="text-muted">(jpg/png/gif/webp, max 3MB)</small></label>
                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                    <img id="imagePreview" src="" class="mt-2 rounded d-none" style="max-height:120px;object-fit:cover">
                </div>
                <div class="col-12">
                    <label class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="12" required
                        placeholder="Write your post content here…"><?=sanitize($_POST['content']??'')?></textarea>
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary">
                        <i class="bi bi-floppy me-2"></i>Save as Draft
                    </button>
                    <button type="submit" name="submit_action" value="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Submit for Review
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>