<?php
/**
 * POST /api/v1/user/upload
 * Gestisce upload avatar/documenti utente
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
require_once __DIR__ . '/../../../../src/models/User.php';
require_once __DIR__ . '/../../../../src/models/AuditLog.php';
require_once __DIR__ . '/../../../../config/app_config.json';

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
    $session = $sessionHandler->validate();
    
    if (EOFsession) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Sessione non valida o scaduta']);
        exit;
    }
    
    $config = json_decode(file_get_contents(__DIR__ . '/../../../../config/app_config.json'), true);
    
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nessun file inviato']);
        exit;
    }
    
    $file = $_FILES['file'];
    $uploadType = $_POST['type'] ?? 'avatar'; // avatar o document
    
    // Validazione base
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Errore upload: ' . $file['error']]);
        exit;
    }
    
    if ($file['size'] > ($config['max_upload_size'] ?? 5242880)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File troppo grande']);
        exit;
    }
    
    // Validazione MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedTypes = $config['allowed_upload_types'] ?? ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tipo file non consentito']);
        exit;
    }
    
    // Validazione estensione
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = $config['allowed_upload_extensions'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($ext, $allowedExts)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Estensione non consentita']);
        exit;
    }
    
    // Determina cartella destinazione
    $uploadPath = $config['upload_path'] ?? '/var/www/html/public/uploads';
    $subDir = $uploadType === 'document' ? 'documents' : 'avatars';
    $targetDir = $uploadPath . '/' . $subDir . '/';
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Genera nome file sicuro
    $filename = 'user_' . $session['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetPath = $targetDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore salvataggio file']);
        exit;
    }
    
    // URL pubblico
    $publicUrl = '/uploads/' . $subDir . '/' . $filename;
    
    // Se avatar, aggiorna utente
    if ($uploadType === 'avatar') {
        $userModel = new User();
        $userModel->update($session['user_id'], ['avatar' => $publicUrl]);
    }
    
    // Log audit
    $auditLog = new AuditLog();
    $auditLog->logUpload($session['user_id'], $file['name'], $publicUrl, true);
    
    echo json_encode([
        'success' => true,
        'url' => $publicUrl,
        'filename' => $filename,
        'message' => 'File caricato con successo'
    ]);
    
} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    
    if (isset($auditLog) && isset($session['user_id'])) {
        $auditLog->logUpload($session['user_id'], $file['name'] ?? 'unknown', '', false);
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
