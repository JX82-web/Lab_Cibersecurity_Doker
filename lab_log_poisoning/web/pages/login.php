<?php
// login.php
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Generiamo l'hash MD5 per combaciare con lo schema strutturato nel DB (init.sql)
    $hash = md5($password);

    $stmt = $pdo->prepare("SELECT * FROM web_users WHERE username = :user AND password_hash = :hash");
    $stmt->execute(['user' => $username, 'hash' => $hash]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        $error = "Credenziali non valide per il portale aziendale.";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card p-4 shadow">
            <h3 class="text-center text-success mb-4">Area Riservata Staff</h3>
            <?php if($error): ?>
                <div class="alert alert-danger py-2"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username Aziendale</label>
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Invia Autenticazione</button>
            </form>
        </div>
    </div>
</div>
