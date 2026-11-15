<?php
require_once __DIR__ . '/config.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
include __DIR__ . '/header.php';
if (!$p): ?><div class="alert">Product not found.</div><?php else: ?>
<div class="card">
<img class="product" src="/assets/<?= e($p['image'] ?? 'laptop.svg') ?>" alt="">
<h1><?= e($p['name']) ?></h1>
<p><?= e($p['description']) ?></p>
<h2>€<?= e($p['price']) ?></h2>
<?php if (!empty($_SESSION['user_id'])): ?>
<form method="post" action="/dashboard.php">
<input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
<input type="hidden" name="action" value="order">
<button>Place training order</button>
</form>
<?php else: ?><a class="btn" href="/login.php">Login to order</a><?php endif; ?>
</div>
<?php endif; include __DIR__ . '/footer.php'; ?>
