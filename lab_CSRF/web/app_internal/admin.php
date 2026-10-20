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

// 1. ELABORAZIONE RICHIESTA PRIVILEGI (Vulnerabile a CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    
    if ($new_role === 'admin' || $new_role === 'user') {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// 2. ELABORAZIONE UPLOAD ARCHIVIO ZIP (Falla RCE per caricare la shell)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_avatar'])) {
    $target_dir = "/var/www/html/uploads/";
    $target_file = $target_dir . basename($_FILES["zip_avatar"]["name"]);
    $ext = pathinfo($target_file, PATHINFO_EXTENSION);

    if (strtolower($ext) === 'zip') {
        if (move_uploaded_file($_FILES["zip_avatar"]["tmp_name"], $target_file)) {
            $zip = new ZipArchive;
            if ($zip->open($target_file) === TRUE) {
                // Estrae tutto in /uploads/ senza controllare i file interni (Vulnerabilità)
                $zip->extractTo($target_dir);
                $zip->close();
                unlink($target_file); 
                $message = "Archivio scompattato con successo in /uploads/!";
            } else {
                $message = "Errore durante l'apertura dell'archivio ZIP.";
            }
        } else {
            $message = "Errore nel caricamento del file sul server.";
        }
    } else {
        $message = "Estensione non consentita. Sono accettati solo archivi .zip!";
    }
}

// Controllo di sicurezza grafico per l'utente loggato nel browser
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die("Accesso negato: Area riservata all'amministratore di sistema.");
}

$result = $conn->query("SELECT id, username, role FROM users");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Amministrazione</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Pannello di Gestione Interna</h2>
    <?php if($message): ?><p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    
    <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
        <tr><th>ID</th><th>Username</th><th>Ruolo attuale</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><strong><?= htmlspecialchars($row['role']) ?></strong></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <hr style="margin-top: 30px;">

    <h3>Caricamento Avatar Profilo / Plugin (.zip)</h3>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="zip_avatar" accept=".zip" required>
        <button type="submit">Invia ed Estrai Archivio</button>
    </form>

    <br>
    <a href="../app/dashboard.php">← Torna alla Dashboard</a>
</body>
</html>
