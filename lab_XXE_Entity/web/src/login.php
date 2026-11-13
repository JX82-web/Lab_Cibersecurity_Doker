<?php
require_once __DIR__ . '/config.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u=trim($_POST['username']??''); $p=$_POST['password']??'';
    $s=$db->prepare("SELECT id,username,password_hash,role FROM users WHERE username=?");
    $s->bind_param('s',$u); $s->execute(); $row=$s->get_result()->fetch_assoc();
    if($row && password_verify($p,$row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']=$row['id']; $_SESSION['username']=$row['username']; $_SESSION['role']=$row['role'];
        header('Location: /dashboard.php'); exit;
    }
    $msg='Invalid credentials.';
}
include __DIR__ . '/header.php'; ?>
<div class="card"><h1>Login</h1>
<?php if(isset($_GET['registered'])): ?><p class="muted">Registration complete. Log in.</p><?php endif; ?>
<?php if($msg): ?><div class="alert"><?=e($msg)?></div><?php endif; ?>
<form method="post"><label>Username</label><input name="username" required>
<label>Password</label><input name="password" type="password" required>
<button>Login</button></form></div>
<?php include __DIR__ . '/footer.php'; ?>
