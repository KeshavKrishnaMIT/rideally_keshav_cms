<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_ADMIN);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        if (!$name) { $error = 'Category name required.'; }
        else {
            $sl = generateSlug($name);
            $stmt = $conn->prepare("INSERT INTO categories (category_name,slug) VALUES (?,?)");
            $stmt->bind_param('ss',$name,$sl);
            $stmt->execute() ? $success = 'Category added.' : $error = 'Failed. Slug may exist.';
            $stmt->close();
        }
    }

    if ($action === 'edit') {
        $id   = (int)$_POST['id'];
        $name = trim($_POST['category_name'] ?? '');
        if (!$name) { $error = 'Category name required.'; }
        else {
            $sl = generateSlug($name);
            $stmt = $conn->prepare("UPDATE categories SET category_name=?,slug=? WHERE id=?");
            $stmt->bind_param('ssi',$name,$sl,$id);
            $stmt->execute() ? $success = 'Updated.' : $error = 'Failed.';
            $stmt->close();
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute() ? $success = 'Deleted.' : $error = 'Failed.';
        $stmt->close();
    }
}

$cats = $conn->query("SELECT c.id,c.category_name,c.slug,c.created_at,
    (SELECT COUNT(*) FROM posts p WHERE p.category_id=c.id) AS post_count
    FROM categories c ORDER BY c.created_at DESC");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-tags me-2 text-primary"></i>Categories</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCatModal"><i class="bi bi-plus-lg me-1"></i>Add Category</button>
</div>

<?php if ($success): ?><div class="alert alert-success" data-auto-dismiss><?= sanitize($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"  data-auto-dismiss><?= sanitize($error) ?></div><?php endif; ?>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Name</th><th>Slug</th><th>Posts</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i=1; while ($c=$cats->fetch_assoc()): ?>
        <tr>
            <td class="text-muted small"><?=$i++?></td>
            <td><?=sanitize($c['category_name'])?></td>
            <td><code style="color:var(--primary);font-size:.8rem"><?=sanitize($c['slug'])?></code></td>
            <td><span class="badge bg-secondary"><?=$c['post_count']?></span></td>
            <td class="text-muted small"><?=date('d M Y',strtotime($c['created_at']))?></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1"
                    data-bs-toggle="modal" data-bs-target="#editCatModal"
                    data-id="<?=$c['id']?>" data-name="<?=sanitize($c['category_name'])?>"><i class="bi bi-pencil"></i></button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?=$c['id']?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this category?"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div></div></div>

<!-- Add Modal -->
<div class="modal fade" id="addCatModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:var(--surface);border-color:var(--border)">
    <div class="modal-header" style="border-color:var(--border)"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="modal-body"><label class="form-label">Category Name</label><input type="text" name="category_name" class="form-control" required></div>
        <div class="modal-footer" style="border-color:var(--border)"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editCatModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="background:var(--surface);border-color:var(--border)">
    <div class="modal-header" style="border-color:var(--border)"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="editCatId">
        <div class="modal-body"><label class="form-label">Category Name</label><input type="text" name="category_name" id="editCatName" class="form-control" required></div>
        <div class="modal-footer" style="border-color:var(--border)"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
document.getElementById('editCatModal').addEventListener('show.bs.modal',function(e){
    const b=e.relatedTarget;
    document.getElementById('editCatId').value=b.dataset.id;
    document.getElementById('editCatName').value=b.dataset.name;
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>