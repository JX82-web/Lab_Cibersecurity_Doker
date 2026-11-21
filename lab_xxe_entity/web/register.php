<?php
session_start();
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users(username, password_hash) VALUES(?,?)")->execute([$_POST['username'], $hash]);
    header('Location: index.php');
    exit;
}
?>
<form method="post">
    <input name="username" placeholder="User"><br><br>
    <input name="password" type="password" placeholder="Pass"><br><br>
    <details>
    	<summary>🔴 exploand to submit button </summary>
    		<button>Register</button>
    </details>
</form>
