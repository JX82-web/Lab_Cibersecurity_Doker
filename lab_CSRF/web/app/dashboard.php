<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$message = '';
if (isset($_POST['submit_report']) && isset($_POST['bug_url'])) {
    $bug_url = $_POST['bug_url'];
    // Salva l'URL nella coda per il bot amministratore
    file_put_contents('/tmp/reported_url.txt', $bug_url);
    $message = 'Segnalazione inviata con successo! L\'amministratore visiterà il link a breve.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef2f3; padding: 40px; }
        .card { background: white; max-width: 500px; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { background: #007BFF; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        .form-control { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h2>Benvenuto, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>Il tuo ruolo attuale è: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px;">
            🎉 Sbloccato: <a href="../app_internal/admin.php"><strong>Pannello Amministrazione</strong></a>
        </p>
    <?php endif; ?>

    <hr>

    <h3>Segnala un bug al supporto (Bot Amministratore)</h3>
    <?php if($message): ?><p class="success"><?= $message ?></p><?php endif; ?>
    <form action="" method="POST">
        <label>Incolla l'URL della pagina vulnerabile (es. il tuo server Python):</label>
        <input type="url" name="bug_url" class="form-control" placeholder="http://172.18.0" required>
        <button type="submit" name="submit_report" class="btn">Invia Link all'Admin</button>
    </form>
    <br>
    <a href="logout.php">Disconnetti</a>
</div>
</body>
</html>
