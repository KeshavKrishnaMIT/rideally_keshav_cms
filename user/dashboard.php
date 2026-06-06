<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_USER);
$user = currentUser();
$uid=(int)$user['id'];
$r=$conn->prepare("SELECT COUNT(*) AS c FROM comments WHERE user_id=?"); $r->bind_param('i',$uid); $r->execute(); $myComments=$r->get_result()->fetch_assoc()['c'];
$r=$conn->query("SELECT COUNT(*) AS c FROM posts WHERE status='approved'"); $totalPosts=$r->fetch_assoc()['c'];

$latest=$conn->query("SELECT p.id,p.title,p.image,p.created_at,u.name AS author,c.category_name
    FROM posts p
    LEFT JOIN users u ON u.id=p.author_id
    LEFT JOIN categories c ON c.id=p.category_id
    WHERE p.status='approved' ORDER BY p.created_at DESC LIMIT 6");

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-newspaper me-2 text-primary"></i>Latest Posts</h1>
    <span class="text-muted small">Welcome, <?=sanitize($user['name'])?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon orange"><i class="bi bi-newspaper"></i></div><div><div class="stat-value"><?=$totalPosts?></div><div class="stat-label">Published Posts</div></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-chat-dots-fill"></i></div><div><div class="stat-value"><?=$myComments?></div><div class="stat-label">My Comments</div></div></div></div>
</div>

<div class="row g-4">
    <?php while($p=$latest->fetch_assoc()):?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <?php if($p['image']):?>
            <img src="<?=UPLOAD_URL.sanitize($p['image'])?>" class="card-img-top" style="height:180px;object-fit:cover" alt="">
            <?php else:?>
            <div class="d-flex align-items-center justify-content-center" style="height:120px;background:var(--surface2);border-radius:var(--radius) var(--radius) 0 0">
                <i class="bi bi-image" style="font-size:2rem;color:var(--text-muted)"></i>
            </div>
            <?php endif;?>
            <div class="card-body">
                <div class="text-muted small mb-2">
                    <span class="badge badge-approved me-1"><?=sanitize($p['category_name']??'General')?></span>
                    <?=date('d M Y',strtotime($p['created_at']))?>
                </div>
                <h6 class="card-title mb-2"><?=sanitize($p['title'])?></h6>
                <p class="text-muted small mb-3">By <?=sanitize($p['author']??'Unknown')?></p>
                <a href="<?=BASE_URL?>user/view_post.php?id=<?=$p['id']?>" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
        </div>
    </div>
    <?php endwhile;?>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>