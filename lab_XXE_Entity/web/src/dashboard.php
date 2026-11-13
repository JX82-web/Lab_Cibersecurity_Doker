<?php
require_once __DIR__ . '/config.php';
require_login();
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='order') {
    $pid=(int)$_POST['product_id'];
    $s=$db->prepare("INSERT INTO orders(user_id,product_id) VALUES(?,?)");
    $s->bind_param('ii',$_SESSION['user_id'],$pid); $s->execute();
    $msg='Order recorded.';
}
include __DIR__ . '/header.php';
?>
<h1>Account dashboard</h1>
<p>Welcome, <strong><?=e($_SESSION['username'])?></strong>.</p>
<?php if($msg): ?><div class="card"><?=e($msg)?></div><?php endif; ?>
<div class="grid">
<div class="card"><h2>Profile</h2><p>Role: <?=e($_SESSION['role'])?></p></div>
<div class="card"><h2>Orders</h2>
<?php
$s=$db->prepare("SELECT o.id,p.name,o.created_at FROM orders o JOIN products p ON p.id=o.product_id WHERE o.user_id=? ORDER BY o.id DESC");
$s->bind_param('i',$_SESSION['user_id']); $s->execute(); $r=$s->get_result();
if($r->num_rows===0) echo '<p class="muted">No orders yet.</p>';
while($o=$r->fetch_assoc()) echo '<p>#'.(int)$o['id'].' — '.e($o['name']).' <small>'.e($o['created_at']).'</small></p>';
?>
</div>
<div class="card"><h2>Operations</h2><p class="muted">Authorized staff can import supplier XML feeds.</p>
<a class="btn" href="/admin/import.php">Supplier XML Import</a></div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
