<?php
    session_start();
    require_once("config.php");

    $product = null;
    $error = null;
    $success = null;

    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // Controlliamo che il prodotto non sia vuoto
    if (!$product_id || $product_id <= 0) {
        $error = "Elemento non valido.";
    }
    // se il numero del prodotto esiste chiama il prodotto.
    else {
        try {
            $prep = $pdo->prepare("SELECT id, name, description, price, image FROM products WHERE id = ?");
            $prep->execute([$product_id]);
            $product = $prep->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                $error = "Elemento non trovato.";
            }
        }
        catch (PDOException $e) {
            $error = "Errore del database.";
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {

        if (!isset($_SESSION['user_id'])) {
            $error = "Effettuaa il login per completare l'acquisto.";
        }
        else {
        // Legge la quantità scelta (minimo 1) e calcola il totale
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $total_price = $product['price'] * $quantity;

            try {
                $prep_order = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity,  total_price) VALUES (?, ?, ?, ?)");
                $prep_order->execute([$_SESSION['user_id'], $product['id'], $quantity, $total_price]);
                $success = "Prenotazione confermata!";
            }
            catch (PDOException $e) {
                $error = "Errore durante la registrazione dell'ordine.";
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
    <a href="index.php">Torna al catalogo</a>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?> <a href="dashboard.php">I tuoi ordini</a></p>
    <?php endif; ?>

    <?php if ($product): ?>
        <div class="game-detail">
            <img src="../assets/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
            <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <p>Prezzo: <?= number_format((float)$product['price'], 2) ?> €</p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    <button type="submit">Acquista ora</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Accedi</a> per acquistare questo articolo.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</body>
</html>
