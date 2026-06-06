<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_ADMIN);

$byStatus = $conn->query("SELECT status,COUNT(*) AS total FROM posts GROUP BY status");
$statusData=[];
while($r=$byStatus->fetch_assoc()) $statusData[$r['status']]=$r['total'];

$byCat = $conn->query("SELECT c.category_name,COUNT(p.id) AS total FROM categories c
    LEFT JOIN posts p ON p.category_id=c.id GROUP BY c.id ORDER BY total DESC LIMIT 10");

$byAuthor = $conn->query("SELECT u.name,COUNT(p.id) AS total FROM users u
    LEFT JOIN posts p ON p.author_id=u.id WHERE u.role='author' GROUP BY u.id ORDER BY total DESC LIMIT 10");

$monthly = $conn->query("SELECT DATE_FORMAT(created_at,'%b %Y') AS month,COUNT(*) AS total FROM posts
    WHERE created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at ASC");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-bar-chart me-2 text-primary"></i>Reports</h1>
</div>

<div class="row g-3 mb-4">
    <?php foreach([POST_DRAFT=>['blue','bi-pencil'],POST_PENDING=>['yellow','bi-hourglass'],POST_APPROVED=>['green','bi-check-circle'],POST_REJECTED=>['red','bi-x-circle']] as $s=>[$col,$icon]):?>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon <?=$col?>"><i class="bi <?=$icon?>"></i></div>
        <div><div class="stat-value"><?=$statusData[$s]??0?></div><div class="stat-label"><?=ucfirst($s)?> Posts</div></div></div>
    </div>
    <?php endforeach;?>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-header"><i class="bi bi-tags me-2"></i>Posts by Category</div>
        <div class="card-body p-0"><table class="table table-hover mb-0">
            <thead><tr><th>Category</th><th>Posts</th></tr></thead><tbody>
            <?php while($r=$byCat->fetch_assoc()):?>
            <tr><td><?=sanitize($r['category_name'])?></td><td><span class="badge bg-secondary"><?=$r['total']?></span></td></tr>
            <?php endwhile;?>
        </tbody></table></div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Posts by Author</div>
        <div class="card-body p-0"><table class="table table-hover mb-0">
            <thead><tr><th>Author</th><th>Posts</th></tr></thead><tbody>
            <?php while($r=$byAuthor->fetch_assoc()):?>
            <tr><td><?=sanitize($r['name'])?></td><td><span class="badge bg-secondary"><?=$r['total']?></span></td></tr>
            <?php endwhile;?>
        </tbody></table></div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-header"><i class="bi bi-calendar3 me-2"></i>Monthly Posts</div>
        <div class="card-body p-0"><table class="table table-hover mb-0">
            <thead><tr><th>Month</th><th>Posts</th></tr></thead><tbody>
            <?php while($r=$monthly->fetch_assoc()):?>
            <tr><td><?=sanitize($r['month'])?></td><td><span class="badge bg-secondary"><?=$r['total']?></span></td></tr>
            <?php endwhile;?>
        </tbody></table></div></div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>