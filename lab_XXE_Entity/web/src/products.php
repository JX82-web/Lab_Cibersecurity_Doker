<?php
require_once __DIR__ . '/config.php';
include __DIR__ . '/header.php';
$result = $db->query("SELECT * FROM products ORDER BY id");
?>
<h1>Products</h1>
<div class="grid">
<?php while ($p = $result->fetch_assoc()): ?>
<div class="card">
<img class="product" src="/assets/<?= e($p['image'] ?? 'laptop.svg') ?>" alt="">
<h2><?= e($p['name']) ?></h2>
<p><?= e($p['description']) ?></p>
<p><strong>€<?= e($p['price']) ?></strong></p>
<a class="btn" href="/product.php?id=<?= (int)$p['id'] ?>">Details</a>
</div>
<?php endwhile; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
