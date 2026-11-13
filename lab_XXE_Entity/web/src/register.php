<?php
require_once __DIR__ . '/config.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u=trim($_POST['username']??''); $p=$_POST['password']??'';
    if (strlen($u)<3 || strlen($p)<6) $msg='Use a username of at least 3 chars and a password of at least 6 chars.';
    else {
        try {
            $hash=password_hash($p,PASSWORD_DEFAULT);
            $s=$db->prepare("INSERT INTO users(username,password_hash) VALUES(?,?)");
            $s->bind_param('ss',$u,$hash); $s->execute();
            header('Location: /login.php?registered=1'); exit;
        } catch (Throwable $e) { $msg='Registration failed. Username may already exist.'; }
    }
}
include __DIR__ . '/header.php'; ?>
<div class="card"><h1>Create account</h1>
<?php if($msg): ?><div class="alert"><?=e($msg)?></div><?php endif; ?>
<form method="post"><label>Username</label><input name="username" required>
<label>Password</label><input name="password" type="password" required>
<button>Register</button></form></div>
<?php include __DIR__ . '/footer.php'; ?>
