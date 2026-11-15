<?php
require_once __DIR__ . '/config.php';
include __DIR__ . '/header.php';
$result = $db->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
?>
<h1>Secure commerce for a deliberately insecure lab.</h1>
<p class="muted">A realistic PHP e-commerce application used for controlled security training.</p>
<div class="grid">
<?php while ($p = $result->fetch_assoc()): ?>
<div class="card">
<img class="product" src="/assets/<?= e($p['image'] ?? 'laptop.svg') ?>" alt="">
<h2><?= e($p['name']) ?></h2>
<p><?= e($p['description']) ?></p>
<strong>€<?= e($p['price']) ?></strong><br><br>
<a class="btn" href="/product.php?id=<?= (int)$p['id'] ?>">View product</a>
</div>
<?php endwhile; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
