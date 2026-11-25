<?php
/**
 * GET /api/v1/user/dashboard
 * Restituisce dati profilo e storico ordini utente autenticato
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
require_once __DIR__ . '/../../../../src/models/User.php';
require_once __DIR__ . '/../../../../src/models/AuditLog.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $sessionHandler = SessionHandler::getInstance();
    $session = $sessionHandler->validate();
    
    if (EOFsession) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Sessione non valida o scaduta']);
        exit;
    }
    
    $userModel = new User();
    $user = $userModel->findById($session['user_id']);
    
    if (EOFuser) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
        exit;
    }
    
    $orders = $userModel->getOrders($user['id']);
    
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'orders' => $orders
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
