<?php
    session_start();
    require_once("config.php");

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Global var
    $error = null;
    $target_user = null;
    $orders = [];

    $target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['user_id'];

    try {
        $prep_cli = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $prep_cli->execute([$target_user_id]);
        $target_user = $prep_cli->fetch(PDO::FETCH_ASSOC);
        $prep_ord = $pdo->prepare("SELECT o.id, o.quantity, o.total_price, o.created_at, p.name AS product_name FROM orders o JOIN products p ON o.product_id = p.id 
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC");

        $prep_ord->execute([$target_user_id]);
        $orders = $prep_ord->fetchAll(PDO::FETCH_ASSOC);
    }

    catch (PDOException $e) {
        $error = "Errore Dati caricamento.";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
    <title>Order</title>
</head>
<body>
    <nav>
    <a href="index.php">Torna al catalogo</a> |
    <a href="logout.php">Logout</a>
</nav>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
    <?php if ($target_user): ?>
    <h1>Dashboard di <?= htmlspecialchars($target_user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Email: <?= htmlspecialchars($target_user['email'], ENT_QUOTES, 'UTF-8') ?></p>
<?php else: ?>
    <p>Utente non trovato.</p>
<?php endif; ?>
    <h2>Storico Ordini</h2>
<?php if (empty($orders)): ?>
    <p>Nessun ordine trovato per questo utente.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID Ordine</th>
                <th>Prodotto</th>
                <th>Quantità</th>
                <th>Totale</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= (int)$order['id'] ?></td>
                    <td><?= htmlspecialchars($order['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$order['quantity'] ?></td>
                    <td><?= number_format((float)$order['total_price'], 2) ?> €</td>
                    <td><?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
