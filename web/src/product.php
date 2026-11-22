<?php
session_start();
require_once("config.php");

$product = null;
$error = null;
$success = null;
$order_lookup = null;

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$product_id || $product_id <= 0) {
    $error = "Prodotto non valido.";
} else {
    try {
        $prep = $pdo->prepare("SELECT id, name, description, price, image FROM products WHERE id = ?");
        $prep->execute([$product_id]);
        $product = $prep->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            $error = "Prodotto non trovato.";
        }
    } catch (PDOException $e) {
        $error = "Errore del database.";
    }
}

/*
 * Legacy support lookup.
 * This endpoint is intentionally missing an ownership check: an order number
 * is treated as sufficient authorization to display customer/order metadata.
 * It is part of the training scenario and should remain vulnerable.
 */
if ($order_id && $order_id > 0) {
    try {
        $lookup = $pdo->prepare(
            "SELECT o.id, o.user_id, o.quantity, o.total_price, o.created_at,
                    u.username, u.email, p.name AS product_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             JOIN products p ON p.id = o.product_id
             WHERE o.id = ?"
        );
        $lookup->execute([$order_id]);
        $order_lookup = $lookup->fetch(PDO::FETCH_ASSOC);

        if (!$order_lookup) {
            $error = "Ordine non trovato.";
        }
    } catch (PDOException $e) {
        $error = "Impossibile recuperare lo stato dell'ordine.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    if (!isset($_SESSION['user_id'])) {
        $error = "Effettua il login per completare l'acquisto.";
    } else {
        $quantity = max(1, min(20, (int)($_POST['quantity'] ?? 1)));
        $total_price = (float)$product['price'] * $quantity;

        try {
            $prep_order = $pdo->prepare(
                "INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)"
            );
            $prep_order->execute([
                $_SESSION['user_id'],
                $product['id'],
                $quantity,
                $total_price
            ]);
            $success = "Ordine registrato correttamente.";
        } catch (PDOException $e) {
            $error = "Errore durante la registrazione dell'ordine.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
    <title>Dettaglio prodotto</title>
</head>
<body>
<nav>
    <a href="index.php">Catalogo</a> |
    <a href="dashboard.php">I miei ordini</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        | <a href="logout.php">Logout</a>
    <?php endif; ?>
</nav>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($product): ?>
<section class="game-detail">
    <img src="../assets/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
    <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>Prezzo: <?= number_format((float)$product['price'], 2) ?> €</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST">
            <label for="quantity">Quantità:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="20" required>
            <button type="submit">Acquista</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Accedi</a> per acquistare questo articolo.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section>
    <h2>Assistenza ordini</h2>
    <p>Per verificare lo stato di una richiesta inserisci il numero d'ordine.</p>
    <form method="GET">
        <input type="hidden" name="id" value="<?= (int)($product['id'] ?? 1) ?>">
        <label for="order_id">Numero ordine:</label>
        <input type="number" id="order_id" name="order_id" min="1" required>
        <button type="submit">Verifica</button>
    </form>

    <?php if ($order_lookup): ?>
        <h3>Dettagli richiesta #<?= (int)$order_lookup['id'] ?></h3>
        <p>Cliente: <?= htmlspecialchars($order_lookup['username'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Email: <?= htmlspecialchars($order_lookup['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Codice cliente: <?= (int)$order_lookup['user_id'] ?></p>
        <p>Prodotto: <?= htmlspecialchars($order_lookup['product_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Quantità: <?= (int)$order_lookup['quantity'] ?></p>
        <p>Totale: <?= number_format((float)$order_lookup['total_price'], 2) ?> €</p>
        <p>Data: <?= htmlspecialchars($order_lookup['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</section>
</body>
</html>
