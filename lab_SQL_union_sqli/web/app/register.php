<?php
require_once 'config.php';

$message = "";
$error = "";

$conn->exec("CREATE TABLE IF NOT EXISTS ctf_users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, password VARCHAR(255))");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("INSERT INTO ctf_users (username, password) VALUES (:username, :password)");
            $stmt->execute(['username' => $username, 'password' => password_hash($password, PASSWORD_BCRYPT)]);
            $message = "Registrazione completata! Puoi effettuare il login.";
        } catch (Exception $e) {
            $error = "Errore: Username già registrato o non valido.";
        }
    } else {
        $error = "Compila tutti i campi.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SecureCorp - Register Account</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #0d0e15; color: #00ff66; padding: 40px; }
        .login-box { max-width: 400px; margin: 100px auto; background: #151821; padding: 30px; border-radius: 8px; border: 1px solid #1f2430; text-align: center; }
        input[type="text"], input[type="password"] { width: 90%; padding: 10px; margin: 15px 0; background: #1f2430; border: 1px solid #2f3647; color: #fff; font-family: monospace; border-radius: 4px; }
        button { background: transparent; color: #00bfff; border: 1px solid #00bfff; padding: 10px 25px; font-weight: bold; cursor: pointer; font-family: monospace; border-radius: 4px; }
        button:hover { background: #00bfff; color: #151821; }
        a { color: #ffb454; text-decoration: none; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="login-box">
    <h2 style="color: #fff; margin-top: 0;">📝 REGISTRAZIONE UTENTE</h2>

    <?php if(!empty($message)): ?><p style="color: #00ff66;"><?php echo $message; ?></p><?php endif; ?>
    <?php if(!empty($error)): ?><p style="color: #ff5555;"><?php echo $error; ?></p><?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Scegli Username" required>
        <input type="password" name="password" placeholder="Scegli Password" required>
        <button type="submit">Crea Account</button>
    </form>
    <br><br>
    <a href="login.php">Hai già un account? Login qui</a>
</div>
</body>
</html>
