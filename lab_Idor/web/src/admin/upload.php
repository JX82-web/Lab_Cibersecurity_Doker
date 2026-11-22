<?php
    session_start();
    require_once("../config.php");

    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    // Global var.
    $error = null;
    $success = null;
    $extracted_files = [];


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
            $error = "Seleziona un file .zip valido.";
        } else {
            $filename = $_FILES['zip_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($ext !== 'zip') {
                $error = "Formato non supportato: sono ammessi solo archivi .zip.";
            } else {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($_FILES['zip_file']['tmp_name']) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $extracted_files[] = $zip->getNameIndex($i);
                    }
                    $zip->extractTo($upload_dir);
                    $zip->close();
                    $success = "Archivio estratto con successo in /admin/uploads/!";
                } else {
                    $error = "Impossibile aprire l'archivio ZIP.";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/style.css">
    <meta charset="UTF-8">
    <title>Upload</title>
</head>
<body>
    <nav>
        <a href="admin.php">Torna al Pannello Admin</a> |
        <a href="../index.php">Negozio</a> |
        <a href="../logout.php">Logout</a>
    </nav>

    <h1>Importa Pacchetto Prodotti (.ZIP)</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($extracted_files)): ?>
        <h3>File estratti in <code>/admin/uploads/</code>:</h3>
        <ul>
    <?php foreach ($extracted_files as $file): ?>
        <li>
         <a href="uploads/<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
            <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
        </a>
        </li>
    <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
    <div>
        <label for="zip_file">Seleziona archivio (.zip):</label>
        <input type="file" id="zip_file" name="zip_file" accept=".zip" required>
    </div>
        <button type="submit">Carica ed Estrai</button>
    </form>

</body>
</html>
