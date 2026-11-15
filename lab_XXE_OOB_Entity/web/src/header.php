<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Northbridge Commerce</title>
<style>
body{margin:0;font-family:Inter,system-ui,sans-serif;background:#0b1020;color:#e7ecff}
nav{background:#121a31;padding:16px 6%;display:flex;gap:20px;align-items:center}
nav a{color:#c9d5ff;text-decoration:none}.brand{font-weight:800;margin-right:auto}
main{max-width:1100px;margin:40px auto;padding:0 20px}
.card{background:#121a31;border:1px solid #273250;border-radius:14px;padding:22px;margin:16px 0}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
input,textarea{width:100%;box-sizing:border-box;padding:11px;margin:7px 0 14px;border-radius:8px;border:1px solid #354467;background:#0e1528;color:white}
button,.btn{background:#4f7cff;color:#fff;border:0;border-radius:8px;padding:11px 16px;text-decoration:none;cursor:pointer}
small,.muted{color:#91a0c5}
.alert{padding:12px;border-radius:8px;background:#2a1830;color:#ffb9d4}
img.product{width:100%;height:160px;object-fit:contain;background:#0e1528;border-radius:10px}
code{background:#0b1020;padding:3px 6px;border-radius:4px}
</style>
</head>
<body>
<nav>
<a class="brand" href="/">Northbridge Commerce</a>
<a href="/products.php">Products</a>
<?php if (!empty($_SESSION['user_id'])): ?>
<a href="/dashboard.php">Dashboard</a>
<a href="/logout.php">Logout</a>
<?php else: ?>
<a href="/login.php">Login</a><a href="/register.php">Register</a>
<?php endif; ?>
</nav>
<main>
