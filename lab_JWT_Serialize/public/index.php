<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

use Lab\Core\Auth;
use Lab\Core\Token;
use Lab\Models\LegacySession;
use Lab\Plugins\AuditPlugin;

$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = (string)($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');

    if (Auth::check($u, $p)) {
        $session = new LegacySession();
        $session->username = $u;
        $session->plugin = new AuditPlugin();
        setcookie('CYBER_TOKEN', Token::issue($session), [
            'httponly' => false,
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        $message = 'Authenticated. Your legacy session token was issued.';
        $messageType = 'is-ok';
    } else {
        $message = 'Invalid credentials.';
        $messageType = 'is-error';
    }
}

$tokenPresent = isset($_COOKIE['CYBER_TOKEN']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cyber Site · Operations Portal</title>
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
      <span class="eyebrow">// Legacy migration interface</span>
      <h1>Operations access, secured by design.</h1>
      <p class="lead">
        Accesso riservato al personale autorizzato. Autenticati per ottenere il token di sessione
        e consultare lo stato dei servizi tramite API v1.
      </p>
      <div class="metrics">
        <div class="metric">
          <div class="label">Service</div>
          <div class="value">cyber_site</div>
        </div>
        <div class="metric">
          <div class="label">API</div>
          <div class="value">v1</div>
        </div>
        <div class="metric">
          <div class="label">Environment</div>
          <div class="value">isolated</div>
        </div>
      </div>
    </section>

    <section class="card" aria-labelledby="login-title">
      <div class="card-head">
        <h2 id="login-title">Sign in</h2>
        <span class="tag">SECURE</span>
      </div>

      <form method="post" autocomplete="off">
        <label class="field">
          <span>Username</span>
          <input name="username" type="text" placeholder="operator id" required>
        </label>
        <label class="field">
          <span>Password</span>
          <input type="password" name="password" placeholder="••••••••" required>
        </label>
        <button class="btn" type="submit">Access portal</button>
      </form>

      <?php if ($message !== ''): ?>
        <div class="notice <?= htmlspecialchars($messageType, ENT_QUOTES) ?>" role="status">
          <?= htmlspecialchars($message, ENT_QUOTES) ?>
        </div>
      <?php endif; ?>

      <?php if ($tokenPresent): ?>
        <div class="notice">
          Legacy session token detected. API status:
          <a href="/api/v1/status.php">/api/v1/status.php</a>
        </div>
      <?php endif; ?>

      <p class="switch">No account yet? <a href="/register.php">Register</a></p>

      <p class="footer-note">LOCAL LAB · AUTHORIZED USE ONLY</p>
    </section>
  </main>
</body>
</html>
