<?php
/**
 * POST /api/v1/auth/login
 * Autenticazione utente e rilascio token sessione
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
require_once __DIR__ . '/../../../../src/models/User.php';
require_once __DIR__ . '/../../../../src/models/AuditLog.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (EOFinput || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username e password richiesti']);
    exit;
}

$username = trim($input['username']);
$password = $input['password'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

try {
    $userModel = new User();
    $auditLog = new AuditLog();
    
    $user = $userModel->verifyCredentials($username, $password);
    
    if (EOFuser) {
        $auditLog->logLogin(null, false, $ip);
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Credenziali non valide']);
        exit;
    }
    
    // Crea sessione
    $sessionHandler = SessionHandler::getInstance();
    $token = $sessionHandler->create($user['id'], [
        'username' => $user['username'],
        'role' => $user['role']
    ]);
    
    $auditLog->logLogin($user['id'], true, $ip);
    
    // Rimuovi password_hash dalla risposta
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => $user,
        'message' => 'Login effettuato con successo'
    ]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
