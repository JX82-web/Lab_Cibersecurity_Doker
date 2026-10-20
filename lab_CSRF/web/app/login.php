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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Modifica Sicura: Uso di Prepared Statements per impedire la SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Verifica la password (in chiaro come da init.sql per scopi didattici)
        if ($password === $user_data['password']) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Credenziali non valide.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; padding: 10px; background-color: #28a745; border: none; color: white; border-radius: 4px; cursor: pointer; }
        .message { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Accedi al Laboratorio</h2>
    <?php if($error): ?><div class="message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form action="" method="POST">
        <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit" class="btn">Login</button>
    </form>
    <p style="text-align:center;"><a href="register.php">Crea un account</a></p>
</div>
</body>
</html>
