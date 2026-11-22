<?php
    session_start();
    require_once("config.php");
    
    $products = [];
    $error = null;
    try {
        // prepare products for databases selections.
        $prepst = $pdo->query("select id, name, description, price, image  from products");

        // selection product from array.
        $products = $prepst->fetchAll(PDO::FETCH_ASSOC);

    }
    catch (PDOException $e){
        $error = "[err] Errore del database, impossibile caricare i dati.";
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <meta charset="UTF-8">
    <title>Welcome to Idor Lab</title>
</head>
<body>
    <nav>
        <!-- home page -->
        <a href="index.php">Home</a>
          <?php if (isset($_SESSION['user_id'])): ?>
    <span>Benvenuto,  Seleziona il tuo prodotto. <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
        <a href="product.php">Product</a>
        <a href="dashboard.php">Dashboard </a> 
        <a href="foother.php">foother</a>
        <a href="logout.php">Logout</a>
    <!-- condizionale else per non mostrare ripetutamente i relativi link sottostamti.-->
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
   <?php endif; ?>

    <!--Area Administrator -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ): ?>
        
        <a href="admin/admin.php">Admin area</a>
        <a href="admin/import.php">import area admin</a>
    <?php endif; ?> 
    </nav>
    <!-- ciclo each per comunicare con il database. -->
    <div class="products">
    
    <?php if($error): ?>
        <p class="error"><?= htmlspecialchars($error,  ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif (empty($products)): ?>
        <p>Nessun prodotto disponibile al momento.</p>
    <?php else: ?>
    <?php foreach ($products as $product) : ?>
        <div class="product">
        <img src="../assets/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8')?>" alt="">
        <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <span>Prezzo: <?= number_format((float)$product['price'], 2) ?> €</span>
        <a href="product.php?id=<?= (int)$product['id'] ?>">Guarda i dettagli</a>
        </div>
   
    <?php endforeach; ?>
    
    <?php endif; ?>

    </div>

</body>
</html>

