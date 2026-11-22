<?php
    session_start();
    require_once("config.php");

    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $error = null;
    // Leggiamo i dati dal database
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // funzione trim torgli spazi inutili
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Compilazione obbligatoria.";
    }
    else {
        try {
            $prepst = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
            $prepst->execute([$username]);
            $user = $prepst->fetch(PDO::FETCH_ASSOC);

        }
        catch (PDOException $e) {
            die("Error from database.");
        }
        if ($user && hash_equals($user['password_hash'], hash('sha256', $password))) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        }
        else {
            $error = "Username o password non validi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
        <h1>Accedi al sito</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
        <form method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <details>
            <summary>🔘Click to expand button</summary>
            <button type="submit">Entra</button>
        </details>
        </form>
            <p><strong>Non hai un account? <a href="register.php">Registrati qui</a></strong></p>
            <p><a href="index.php">Torna alla Home</a></p>
</body>
</html>



