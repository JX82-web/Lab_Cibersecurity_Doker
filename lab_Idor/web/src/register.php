<?php
    session_start();
    require_once("config.php");

    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1. Lettura dei dati dal form
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

    // Controlliamo che tutti i campi non siano vuoti.
    if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {

        $error = "Compila tutti i campi.";
    }
    // Controlliamo che l'indirizzo emaail sia valido.
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Indirizzo email non valido.";
    }

    // Controllo sulle password registrate coincidano
    elseif ($password !== $confirm_password) {
            $error = "Password non valida.";
    }
    // se tutti i controlli su sono validi interroghiamo e salviamo le nuove passwd nel database.
    else {
        try {
            // 1. Verifica che username o email non siano già presenti nella tabella
            $verify = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $verify->execute([$username, $email]);

            if ($verify->fetch()) {
                $error = "Error, username ed email già registrati.";
            }
            else {
                // 2. Calcola l'hash SHA-256 della password e salva il nuovo record
                $password_hash = hash('sha256', $password);
                $password = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $password->execute([$username, $email, $password_hash]);

                $success = "Registrazione completata con successo!";
            }
        }
        catch (PDOException $e) {
            $error = "Errore durante il salvataggio.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
        <style>

        </style>
        <title>Register</title>
</head>
<body>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    
    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?> <a href="login.php">Vai al login</a></p>
    <?php endif; ?>
        <form method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
         <div>
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div>
            <label for="confirm_password">Password_confirm:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <details>
            <summary>⚠️ Click to expand button for send requests ⚠️</summary>
            <button type="submit">Entra</button>
        </details>
        </form>
            <p><strong> Login<a href="login.php">Login</a></strong></p>
            
            <p><a href="index.php">Torna alla Home</a></p>
</body>
</html>
