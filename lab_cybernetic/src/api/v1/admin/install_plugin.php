<?php
/**
 * POST /api/v1/admin/install_plugin
 * Estrae e registra nuovi plugin (.zip o directory)
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/SessionHandler.php';
require_once __DIR__ . '/../../../../src/core/PluginLoader.php';
require_once __DIR__ . '/../../../../src/models/AuditLog.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
    
    $loader = PluginLoader::getInstance();
    $auditLog = new AuditLog();
    $adminId = $session['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
        exit;
    }
    
    // Supporta sia upload file ZIP che JSON con path
    $pluginId = null;
    
    if (isset($_FILES['plugin']) && $_FILES['plugin']['error'] === UPLOAD_ERR_OK) {
        // Upload ZIP
        $tmpPath = $_FILES['plugin']['tmp_name'];
        
        // Validazione base: deve essere ZIP
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        
        if ($mime !== 'application/zip' && $mime !== 'application/x-zip-compressed') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File deve essere archivio ZIP']);
            exit;
        }
        
        $pluginId = $loader->installPlugin($tmpPath);
    }
    elseif (isset($_POST['plugin_path'])) {
        // Path diretto (per testing)
        $pluginId = $loader->installPlugin($_POST['plugin_path']);
    }
    else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Plugin richiesto (file ZIP o plugin_path)']);
        exit;
    }
    
    $auditLog->logPluginInstall($adminId, $pluginId, true);
    
    echo json_encode([
        'success' => true,
        'plugin_id' => $pluginId,
        'message' => 'Plugin installato e attivato con successo'
    ]);
    
} catch (Exception $e) {
    error_log("Install plugin error: " . $e->getMessage());
    
    if (isset($auditLog) && isset($adminId) && isset($pluginId)) {
        $auditLog->logPluginInstall($adminId, $pluginId, false);
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
