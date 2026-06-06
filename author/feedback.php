<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireRole(ROLE_AUTHOR);

$uid = (int)$user['id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt=$conn->prepare("SELECT p.*,e.name AS editor_name FROM posts p
    LEFT JOIN users e ON e.id=p.reviewed_by
    WHERE p.id=? AND p.author_id=? AND p.status='rejected'");
$stmt->bind_param('ii',$id,$uid); $stmt->execute();
$post=$stmt->get_result()->fetch_assoc(); $stmt->close();

if (!$post) { header('Location:'.BASE_URL.'author/posts.php'); exit; }

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-chat-text me-2 text-primary"></i>Editor Feedback</h1>
    <a href="<?=BASE_URL?>author/posts.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i><?=sanitize($post['title'])?></div>
            <div class="card-body">
                <div class="alert alert-danger mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-x-circle-fill"></i>
                        <strong>Post Rejected</strong>
                        <?php if($post['editor_name']):?>
                        <span class="text-muted small">by <?=sanitize($post['editor_name'])?></span>
                        <?php endif;?>
                        <?php if($post['reviewed_at']):?>
                        <span class="text-muted small ms-auto"><?=date('d M Y, h:i A',strtotime($post['reviewed_at']))?></span>
                        <?php endif;?>
                    </div>
                    <p class="mb-0"><?=sanitize($post['editor_feedback']??'No feedback provided.')?></p>
                </div>
                <a href="<?=BASE_URL?>author/edit_post.php?id=<?=$post['id']?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Edit & Resubmit
                </a>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>