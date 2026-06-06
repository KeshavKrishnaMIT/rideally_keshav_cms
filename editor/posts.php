<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_EDITOR);

$filter = $_GET['status'] ?? 'pending';
$search = trim($_GET['search'] ?? '');

$where = []; $params = []; $types = '';
if ($filter !== '') { $where[] = "p.status=?"; $params[] = $filter; $types .= 's'; }
if ($search !== '') { $like="%$search%"; $where[]="(p.title LIKE ? OR u.name LIKE ?)"; $params[]=$like; $params[]=$like; $types.='ss'; }

$sql = "SELECT p.id,p.title,p.status,p.created_at,u.name AS author,c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id=p.author_id
    LEFT JOIN categories c ON c.id=p.category_id"
    .($where?' WHERE '.implode(' AND ',$where):'')." ORDER BY p.created_at DESC";

if ($params) { $stmt=$conn->prepare($sql); $stmt->bind_param($types,...$params); $stmt->execute(); $posts=$stmt->get_result(); }
else { $posts=$conn->query($sql); }

$statuses = [''=> 'All', POST_PENDING=>'Pending', POST_APPROVED=>'Approved', POST_REJECTED=>'Rejected'];

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Review Posts</h1>
</div>

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
    <input type="text" name="search" class="form-control" style="max-width:260px" placeholder="Search…" value="<?=sanitize($search)?>">
    <select name="status" class="form-select" style="max-width:160px">
        <?php foreach($statuses as $v=>$l):?><option value="<?=$v?>" <?=$filter===$v?'selected':''?>><?=$l?></option><?php endforeach;?>
    </select>
    <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Filter</button>
    <?php if($filter||$search):?><a href="<?=BASE_URL?>editor/posts.php" class="btn btn-outline-secondary">Clear</a><?php endif;?>
</form>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
        <?php $i=1; while($p=$posts->fetch_assoc()):?>
        <tr>
            <td class="text-muted small"><?=$i++?></td>
            <td style="max-width:220px"><span class="d-inline-block text-truncate w-100"><?=sanitize($p['title'])?></span></td>
            <td class="text-muted small"><?=sanitize($p['author']??'N/A')?></td>
            <td class="text-muted small"><?=sanitize($p['category_name']??'—')?></td>
            <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
            <td class="text-muted small"><?=date('d M Y',strtotime($p['created_at']))?></td>
            <td>
                <a href="<?=BASE_URL?>editor/review_post.php?id=<?=$p['id']?>" class="btn btn-sm <?=$p['status']==='pending'?'btn-primary':'btn-outline-secondary'?>">
                    <i class="bi bi-<?=$p['status']==='pending'?'pencil-square':'eye'?> me-1"></i><?=$p['status']==='pending'?'Review':'View'?>
                </a>
            </td>
        </tr>
        <?php endwhile;?>
        </tbody>
    </table>
</div></div></div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>