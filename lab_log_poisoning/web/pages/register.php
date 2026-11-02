<?php
// register.php
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $hash = md5($password);
        try {
            $stmt = $pdo->prepare("INSERT INTO web_users (username, email, password_hash, role) VALUES (:user, :email, :hash, 'user')");
            $stmt->execute(['user' => $username, 'email' => $email, 'hash' => $hash]);
            $success = "Registrazione completata con successo! Ora puoi accedere.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Errore: Username o Email già esistenti nel database.";
            } else {
                $error = "Errore database: " . $e->getMessage();
            }
        }
    } else {
        $error = "Compila tutti i campi obbligatori.";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card p-4 shadow">
            <h3 class="text-center text-light mb-4">Crea Account Fornitore</h3>
            <?php if($success): ?> <div class="alert alert-success py-2"><?= $success ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="alert alert-danger py-2"><?= $error ?></div> <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Aziendale</label>
                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrati</button>
            </form>
        </div>
    </div>
</div>
