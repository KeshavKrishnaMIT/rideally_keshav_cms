<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_SUPER_ADMIN);

$success = $error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add user
    if ($action === 'add') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role'] ?? ROLE_USER;
        $status   = $_POST['status'] ?? USER_ACTIVE;

        if (!$name || !$email || !$password) {
            $error = 'Name, email and password are required.';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
            $chk->bind_param('s', $email); $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = 'Email already exists.';
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,?,?)");
                $stmt->bind_param('sssss', $name, $email, $password, $role, $status);
                $stmt->execute() ? $success = 'User added successfully.' : $error = 'Failed to add user.';
                $stmt->close();
            }
            $chk->close();
        }
    }

    // Edit user
    if ($action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $role   = $_POST['role'] ?? ROLE_USER;
        $status = $_POST['status'] ?? USER_ACTIVE;
        $pw     = trim($_POST['password'] ?? '');

        if (!$name || !$email) { $error = 'Name and email required.'; }
        else {
            if ($pw !== '') {
                $stmt = $conn->prepare("UPDATE users SET name=?,email=?,password=?,role=?,status=? WHERE id=?");
                $stmt->bind_param('sssssi', $name, $email, $pw, $role, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?,email=?,role=?,status=? WHERE id=?");
                $stmt->bind_param('ssssi', $name, $email, $role, $status, $id);
            }
            $stmt->execute() ? $success = 'User updated.' : $error = 'Update failed.';
            $stmt->close();
        }
    }

    // Delete user
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$user['id']) { $error = 'Cannot delete yourself.'; }
        else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute() ? $success = 'User deleted.' : $error = 'Delete failed.';
            $stmt->close();
        }
    }
}

// Fetch all users
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = "%$search%";
    $stmt = $conn->prepare("SELECT id,name,email,role,status,created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param('ss', $like, $like); $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query("SELECT id,name,email,role,status,created_at FROM users ORDER BY created_at DESC");
}

$roles    = [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_EDITOR, ROLE_AUTHOR, ROLE_USER];
$statuses = [USER_ACTIVE, USER_INACTIVE, USER_BANNED];

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>Manage Users</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </button>
</div>

<?php if ($success): ?><div class="alert alert-success" data-auto-dismiss><?= sanitize($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"  data-auto-dismiss><?= sanitize($error) ?></div><?php endif; ?>

<!-- Search -->
<form method="GET" class="mb-3 d-flex gap-2" style="max-width:420px">
    <input type="text" name="search" class="form-control" placeholder="Search by name or email…" value="<?= sanitize($search) ?>">
    <button class="btn btn-outline-primary px-3"><i class="bi bi-search"></i></button>
    <?php if ($search): ?><a href="<?= BASE_URL ?>super_admin/users.php" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a><?php endif; ?>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php $i=1; while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted small"><?= $i++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-circle"><?= strtoupper(substr($u['name'],0,1)) ?></span>
                            <?= sanitize($u['name']) ?>
                        </div>
                    </td>
                    <td class="text-muted small"><?= sanitize($u['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= sanitize($u['role']) ?></span></td>
                    <td><span class="badge <?= $u['status']==='active' ? 'badge-approved' : ($u['status']==='banned' ? 'badge-rejected' : 'badge-pending') ?>"><?= $u['status'] ?></span></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                            data-id="<?= $u['id'] ?>"
                            data-name="<?= sanitize($u['name']) ?>"
                            data-email="<?= sanitize($u['email']) ?>"
                            data-role="<?= $u['role'] ?>"
                            data-status="<?= $u['status'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php if ($u['id'] != $user['id']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete user <?= sanitize($u['name']) ?>?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--surface);border-color:var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <?php foreach ($roles as $r): ?><option value="<?= $r ?>"><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--surface);border-color:var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="editEmail" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label><input type="password" name="password" class="form-control"></div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="editRole" class="form-select">
                            <?php foreach ($roles as $r): ?><option value="<?= $r ?>"><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editUserModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value     = btn.dataset.id;
    document.getElementById('editName').value   = btn.dataset.name;
    document.getElementById('editEmail').value  = btn.dataset.email;
    document.getElementById('editRole').value   = btn.dataset.role;
    document.getElementById('editStatus').value = btn.dataset.status;
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>