<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

use Lab\Core\Auth;

$message = '';
$messageType = '';
$success = false;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm'] ?? '');

    try {
        if ($password !== $confirm) {
            throw new RuntimeException('Passwords do not match.');
        }
        Auth::register($username, $password);
        $success = true;
        $message = 'Account created. You can now sign in.';
        $messageType = 'is-ok';
        $username = '';
    } catch (RuntimeException $e) {
        $message = $e->getMessage();
        $messageType = 'is-error';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cyber Site · Register</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="bg-grid" aria-hidden="true"></div>
  <div class="orb orb-1" aria-hidden="true"></div>
  <div class="orb orb-2" aria-hidden="true"></div>
  <div class="orb orb-3" aria-hidden="true"></div>

  <header class="topbar">
    <div class="brand">
      <div class="brand-mark" aria-hidden="true">CS</div>
      <div class="brand-name">
        Cyber Site
        <small>Internal Operations Portal</small>
      </div>
    </div>
    <span class="status-pill">SYSTEMS ONLINE</span>
  </header>

  <main class="layout">
    <section class="hero">
      <span class="eyebrow">// New operator enrollment</span>
      <h1>Request portal access.</h1>
      <p class="lead">
        Crea un account operatore per accedere al portale. Le password sono salvate in forma cifrata.
      </p>
    </section>

    <section class="card" aria-labelledby="register-title">
      <div class="card-head">
        <h2 id="register-title">Register</h2>
        <span class="tag">NEW</span>
      </div>

      <form method="post" autocomplete="off">
        <label class="field">
          <span>Username</span>
          <input name="username" type="text" value="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
                 placeholder="3–32 characters" minlength="3" maxlength="32" required>
        </label>
        <label class="field">
          <span>Password</span>
          <input type="password" name="password" placeholder="at least 8 characters"
                 minlength="8" maxlength="128" required>
        </label>
        <label class="field">
          <span>Confirm password</span>
          <input type="password" name="confirm" placeholder="repeat password"
                 minlength="8" maxlength="128" required>
        </label>
        <button class="btn" type="submit">Create account</button>
      </form>

      <?php if ($message !== ''): ?>
        <div class="notice <?= htmlspecialchars($messageType, ENT_QUOTES) ?>" role="status">
          <?= htmlspecialchars($message, ENT_QUOTES) ?>
        </div>
      <?php endif; ?>

      <p class="switch">
        <?php if ($success): ?>
          <a href="/index.php">Go to sign in →</a>
        <?php else: ?>
          Already have an account? <a href="/index.php">Sign in</a>
        <?php endif; ?>
      </p>

      <p class="footer-note">LOCAL LAB · AUTHORIZED USE ONLY</p>
    </section>
  </main>
</body>
</html>
