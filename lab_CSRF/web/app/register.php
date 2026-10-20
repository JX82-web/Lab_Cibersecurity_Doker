<?php
session_start();

$host = 'database'; 
$db   = 'csrf_db';  
$user = 'root';     
$pass = 'lovesucks1'; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Tutti i campi sono obbligatori.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $check->bind_param('s', $username);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Questo username è già registrato.';
        } else {
            // Vulnerabilità voluta: Password salvata in chiaro coerente con init.sql
            $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, "user")');
            $stmt->bind_param('ss', $username, $password);
            if ($stmt->execute()) {
                $success = 'Registrazione completata! Ora puoi effettuare il login.';
            } else {
                $error = 'Errore durante la registrazione.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; padding: 10px; background-color: #007BFF; border: none; color: white; border-radius: 4px; cursor: pointer; }
        .message { color: red; text-align: center; margin-bottom: 15px; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Registrazione</h2>
    <?php if($error): ?><div class="message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="message success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form action="" method="POST">
        <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit" class="btn">Registrati</button>
    </form>
    <p style="text-align:center;"><a href="login.php">Accedi qui</a></p>
</div>
</body>
</html>
