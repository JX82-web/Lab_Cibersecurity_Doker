<?php
require_once 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM ctf_users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Credenziali non valide.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SecureCorp - Employee Access</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #0d0e15; color: #00ff66; padding: 40px; }
        .login-box { max-width: 400px; margin: 100px auto; background: #151821; padding: 30px; border-radius: 8px; border: 1px solid #1f2430; text-align: center; }
        input[type="text"], input[type="password"] { width: 90%; padding: 10px; margin: 15px 0; background: #1f2430; border: 1px solid #2f3647; color: #fff; font-family: monospace; border-radius: 4px; }
        button { background: transparent; color: #ffb454; border: 1px solid #ffb454; padding: 10px 25px; font-weight: bold; cursor: pointer; font-family: monospace; border-radius: 4px; }
        button:hover { background: #ffb454; color: #151821; }
        a { color: #00bfff; text-decoration: none; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="login-box">
    <h2 style="color: #fff; margin-top: 0;">🔒 AREA DIPENDENTI</h2>

    <?php if(!empty($error)): ?><p style="color: #ff5555;"><?php echo $error; ?></p><?php endif; ?>

    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Autenticati</button>
    </form>
    <br><br>
    <a href="register.php">Non hai un account? Registrati</a> | <a href="index.php">Torna al catalogo</a>
</div>
</body>
</html>
