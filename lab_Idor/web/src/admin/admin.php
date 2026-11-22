<?php
    session_start();
    require_once("../config.php");

    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
    // Global var.
    $error = null;
    $users = [];
    $all_orders = [];

    // prepare to query
    try {

        $users_stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
        $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders_stmt = $pdo->query("SELECT o.id, o.quantity, o.total_price, o.created_at, u.username, p.name AS product_name FROM orders o
        JOIN users u ON o.user_id = u.id JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC");

        $all_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    catch (PDOException $e) {
        $error = "Errore nel caricamento del databaase.";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/style.css">
    <meta charset="UTF-8">
    <title>Admin Panel</title>
</head>
<body>
    <nav>
        <a href="../index.php">Torna al negozio</a> |
        <a href="upload.php">Carica Archivio Prodotti (.ZIP)</a> |
        <a href="../logout.php">Logout</a>
    </nav>

    <h1>Panel Admin</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <section>
        <h2>Gestione Importazione Massiva</h2>
        <p>Carica un archivio <strong>.zip</strong> per aggiornare i file e i pacchetti del catalogo:</p>
        <a href="upload.php"><button type="button">Apri Upload Archivio (.ZIP)</button></a>
    </section>
        <h2>Utenti Registrati</h2>
    <table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Ruolo</th>
            <th>Registrato il</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
         <h2>Tutti gli Ordini</h2>
    <table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>ID Ordine</th>
            <th>Utente</th>
            <th>Prodotto</th>
            <th>Quantità</th>
            <th>Totale</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($all_orders as $ord): ?>
            <tr>
                <td><?= (int)$ord['id'] ?></td>
                <td><?= htmlspecialchars($ord['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($ord['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$ord['quantity'] ?></td>
                <td><?= number_format((float)$ord['total_price'], 2) ?> €</td>
                <td><?= htmlspecialchars($ord['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>

</body>
</html>
