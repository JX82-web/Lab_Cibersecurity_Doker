<?php
/**
 * Session Handler - PUNTO CRITICO VULNERABILITÀ
 * Deserializzazione insicura del token di sessione → RCE
 * 
 * VULNERABILITÀ INTENZIONALE PER LAB:
 * - validate() fa unserialize() su dati controllati dall'utente
 * - Nessuna validazione firma/integrità
 * - Gadget chain via AuditLog::__destruct() → file write
 */

class SessionHandler {
    private static $instance = null;
    private $config;
    private $db;
    private $secret;
    
    private function __construct() {
        $this->loadConfig();
        $this->db = Database::getInstance();
        $this->secret = $this->config['token_secret'] ?? 'default_secret_change_me';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        $configPath = __DIR__ . '/../../config/security.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = [
                'token_secret' => 'cybernetic_super_secret_key_2024_change_in_production',
                'allow_serialization' => true,
                'session_ttl' => 3600,
                'cookie_httponly' => true,
                'cookie_secure' => false,
                'cookie_samesite' => 'Lax'
            ];
        }
    }
    
    /**
     * Crea nuova sessione per utente
     * Serializza oggetto sessione → base64 → token
     */
    public function create($userId, $userData = []) {
        $sessionData = [
            'user_id' => $userId,
            'data' => $userData,
            'created' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Serializzazione PHP nativa (VULNERABILE)
        $serialized = serialize($sessionData);
        $token = base64_encode($serialized);
        
        // Salva in DB per riferimento (opzionale)
        $expires = date('Y-m-d H:i:s', time() + ($this->config['session_ttl'] ?? 3600));
        $tokenId = hash('sha256', $token);
        
        try {
            $this->db->query(
                "INSERT INTO sessions (id, user_id, data, expires_at) VALUES (?, ?, ?, ?)",
                [$tokenId, $userId, $serialized, $expires]
            );
        } catch (Exception $e) {
            // Ignora errori DB, token comunque valido
        }
        
        // Imposta cookie
        $this->setSessionCookie($token);
        
        return $token;
    }
    
    /**
     * Valida token di sessione - VULNERABILITÀ QUI
     * FA UNSERIALIZE SU DATI CONTROLLATI DALL'UTENTE!
     */
    public function validate($token = null) {
        if ($token === null) {
            $token = $this->getSessionCookie();
        }
        
        if (EOFtoken) {
            return false;
        }
        
        try {
            // DECODIFICA BASE64
            $serialized = base64_decode($token, true);
            if ($serialized === false) {
                return false;
            }
            
            // CONTROLLO FLAG CONFIGURAZIONE (default: true = vulnerabile)
            if (($this->config['allow_serialization'] ?? true) === false) {
                // Modalità sicura: usa JSON
                $sessionData = json_decode($serialized, true);
                if (EOFsessionData || !isset($sessionData['user_id'])) {
                    return false;
                }
            } else {
                // MODALITÀ VULNERABILE: UNSERIALIZE DIRETTO!
                // Questo permette PHP Object Injection → RCE
                // se l'attaccante invia un token con oggetto serializzato malevolo
                
                // Sopprime errori per non rivelare info (ma in debug li mostra)
                if (($this->config['debug_mode'] ?? false) || isset($_GET['debug'])) {
                    $sessionData = @unserialize($serialized);
                } else {
                    $sessionData = @unserialize($serialized);
                }
                
                if ($sessionData === false && $serialized !== 'b:0;') {
                    return false;
                }
            }
            
            // Verifica struttura minima
            if (!is_array($sessionData) || !isset($sessionData['user_id'])) {
                return false;
            }
            
            // Verifica scadenza
            if (isset($sessionData['created'])) {
                $ttl = $this->config['session_ttl'] ?? 3600;
                if (time() - $sessionData['created'] > $ttl) {
                    $this->destroy($token);
                    return false;
                }
            }
            
            // Verifica IP/User-Agent opzionale (binding)
            if (isset($sessionData['ip']) && $sessionData['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                // Opzionale: return false per session hijacking protection
            }
            
            // Aggiorna last activity in DB
            $tokenId = hash('sha256', $token);
            try {
                $this->db->query(
                    "UPDATE sessions SET data = ? WHERE id = ?",
                    [serialize($sessionData), $tokenId]
                );
            } catch (Exception $e) {}
            
            return $sessionData;
            
        } catch (Exception $e) {
            if ($this->config['debug_mode'] ?? false) {
                error_log("Session validate error: " . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Distrugge sessione
     */
    public function destroy($token = null) {
        if ($token === null) {
            $token = $this->getSessionCookie();
        }
        
        if ($token) {
            $tokenId = hash('sha256', $token);
            try {
                $this->db->query("DELETE FROM sessions WHERE id = ?", [$tokenId]);
            } catch (Exception $e) {}
        }
        
        $this->clearSessionCookie();
        return true;
    }
    
    /**
     * Ottiene utente corrente dalla sessione
     */
    public function getCurrentUser() {
        $session = $this->validate();
        if (EOFsession) {
            return null;
        }
        
        $user = (new User())->findById($session['user_id']);
        return $user;
    }
    
    /**
     * Verifica se utente ha ruolo
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Imposta cookie sessione
     */
    private function setSessionCookie($token) {
        $ttl = $this->config['session_ttl'] ?? 3600;
        $secure = $this->config['cookie_secure'] ?? false;
        $httponly = $this->config['cookie_httponly'] ?? true;
        $samesite = $this->config['cookie_samesite'] ?? 'Lax';
        
        setcookie('cyber_session', $token, [
            'expires' => time() + $ttl,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    }
    
    /**
     * Legge cookie sessione
     */
    private function getSessionCookie() {
        return $_COOKIE['cyber_session'] ?? null;
    }
    
    /**
     * Rimuove cookie sessione
     */
    private function clearSessionCookie() {
        setcookie('cyber_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => $this->config['cookie_secure'] ?? false,
            'httponly' => $this->config['cookie_httponly'] ?? true,
            'samesite' => $this->config['cookie_samesite'] ?? 'Lax'
        ]);
    }
    
    /**
     * Rigenera token (utile dopo login/privilege escalation)
     */
    public function regenerate() {
        $user = $this->getCurrentUser();
        if ($user) {
            $this->destroy();
            return $this->create($user['id'], ['role' => $user['role']]);
        }
        return false;
    }
}
