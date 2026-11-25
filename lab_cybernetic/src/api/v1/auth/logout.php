<?php
/**
 * POST /api/v1/auth/logout
 * Distrugge sessione utente
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
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

try {
    $sessionHandler = SessionHandler::getInstance();
    $user = $sessionHandler->getCurrentUser();
    
    if ($user) {
        $auditLog = new AuditLog();
        $auditLog->logLogout($user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
    
    $sessionHandler->destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout effettuato con successo'
    ]);
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
