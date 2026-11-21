<?php
session_start();
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username=?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Credenziali errate';
}
?>
<form method="post">
    <input name="username" placeholder="User"><br><br>
    <input name="password" type="password" placeholder="Pass"><br><br>
    <details>
    	<summary>😁 click to expand </summary>
    		<button>Login</button>
    </details>
</form>
<?php if(isset($error)) echo "$error"; ?>
<a href="register.php">Register</a>
