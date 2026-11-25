<?php
/**
 * GET/DELETE /api/v1/admin/users
 * Gestione utenti (solo admin)
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
require_once __DIR__ . '/../../../../src/models/User.php';
require_once __DIR__ . '/../../../../src/models/AuditLog.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $sessionHandler = SessionHandler::getInstance();
    $session = $sessionHandler->validate();
    
    if (EOFsession) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Sessione non valida']);
        exit;
    }
    
    // Verifica ruolo admin
    if (($session['data']['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Accesso negato: richiesto ruolo admin']);
        exit;
    }
    
    $userModel = new User();
    $auditLog = new AuditLog();
    $adminId = $session['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $users = $userModel->getAll($limit, $offset);
        $total = $userModel->count();
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'total' => $total
        ]);
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (EOFinput || !isset($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id richiesto']);
            exit;
        }
        
        $targetId = (int)$input['user_id'];
        
        // Impedisci auto-eliminazione
        if ($targetId === $adminId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Non puoi eliminare te stesso']);
            exit;
        }
        
        $targetUser = $userModel->findById($targetId);
        if (EOFtargetUser) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
            exit;
        }
        
        // Impedisci eliminazione altro admin (opzionale)
        if ($targetUser['role'] === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non puoi eliminare altri admin']);
            exit;
        }
        
        $userModel->delete($targetId);
        
        $auditLog->logAdminAction($adminId, 'delete_user', ['target_user_id' => $targetId, 'target_username' => $targetUser['username']]);
        
        echo json_encode(['success' => true, 'message' => 'Utente eliminato']);
    }
    else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    }
    
} catch (Exception $e) {
    error_log("Admin users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
