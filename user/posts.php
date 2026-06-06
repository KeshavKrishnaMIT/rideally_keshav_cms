<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_USER);

$search = trim($_GET['search'] ?? '');
$catFilter = (int)($_GET['category'] ?? 0);

$where=["p.status='approved'"]; $params=[]; $types='';
if ($search!=='') { $like="%$search%"; $where[]="(p.title LIKE ? OR u.name LIKE ?)"; $params[]=$like; $params[]=$like; $types.='ss'; }
if ($catFilter>0) { $where[]="p.category_id=?"; $params[]=$catFilter; $types.='i'; }

$sql="SELECT p.id,p.title,p.image,p.created_at,u.name AS author,c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id=p.author_id
    LEFT JOIN categories c ON c.id=p.category_id
    WHERE ".implode(' AND ',$where)." ORDER BY p.created_at DESC";

if ($params) { $stmt=$conn->prepare($sql); $stmt->bind_param($types,...$params); $stmt->execute(); $posts=$stmt->get_result(); }
else { $posts=$conn->query($sql); }

$cats=$conn->query("SELECT id,category_name FROM categories ORDER BY category_name ASC");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-newspaper me-2 text-primary"></i>Browse Posts</h1>
</div>

<form method="GET" class="d-flex flex-wrap gap-2 mb-4">
    <input type="text" name="search" class="form-control" style="max-width:260px" placeholder="Search posts…" value="<?=sanitize($search)?>">
    <select name="category" class="form-select" style="max-width:200px">
        <option value="0">All Categories</option>
        <?php while($c=$cats->fetch_assoc()):?>
        <option value="<?=$c['id']?>" <?=$catFilter==$c['id']?'selected':''?>><?=sanitize($c['category_name'])?></option>
        <?php endwhile;?>
    </select>
    <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Search</button>
    <?php if($search||$catFilter):?><a href="<?=BASE_URL?>user/posts.php" class="btn btn-outline-secondary">Clear</a><?php endif;?>
</form>

<div class="row g-4">
    <?php $count=0; while($p=$posts->fetch_assoc()): $count++;?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <?php if($p['image']):?>
            <img src="<?=UPLOAD_URL.sanitize($p['image'])?>" class="card-img-top" style="height:180px;object-fit:cover" alt="">
            <?php else:?>
            <div class="d-flex align-items-center justify-content-center" style="height:120px;background:var(--surface2);border-radius:var(--radius) var(--radius) 0 0">
                <i class="bi bi-image" style="font-size:2rem;color:var(--text-muted)"></i>
            </div>
            <?php endif;?>
            <div class="card-body d-flex flex-column">
                <div class="text-muted small mb-2">
                    <?php if($p['category_name']):?><span class="badge badge-approved me-1"><?=sanitize($p['category_name'])?></span><?php endif;?>
                    <?=date('d M Y',strtotime($p['created_at']))?>
                </div>
                <h6 class="card-title flex-grow-1"><?=sanitize($p['title'])?></h6>
                <p class="text-muted small mb-3">By <?=sanitize($p['author']??'Unknown')?></p>
                <a href="<?=BASE_URL?>user/view_post.php?id=<?=$p['id']?>" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
        </div>
    </div>
    <?php endwhile;?>
    <?php if($count===0):?>
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-inbox" style="font-size:3rem"></i>
        <p class="mt-2">No posts found.</p>
    </div>
    <?php endif;?>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>