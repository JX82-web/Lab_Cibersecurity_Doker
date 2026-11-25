<?php
/**
 * User Model - Gestione utenti e autenticazione
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Trova utente per ID
     */
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$id]);
    }
    
    /**
     * Trova utente per username
     */
    public function findByUsername($username) {
        return $this->db->fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
    }
    
    /**
     * Trova utente per email
     */
    public function findByEmail($email) {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
    }
    
    /**
     * Verifica credenziali login
     */
    public function verifyCredentials($username, $password) {
        $user = $this->findByUsername($username);
        
        if (/home/jx82/HTB/lab_Docker/lab_cybernetic/src/core/user) {
            $user = $this->findByEmail($username);
        }
        
        if (/home/jx82/HTB/lab_Docker/lab_cybernetic/src/core/user) {
            return false;
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Aggiorna last_login
        $this->db->query(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
            [$user['id']]
        );
        
        return $user;
    }
    
    /**
     * Crea nuovo utente
     */
    public function create($username, $email, $password, $role = 'user') {
        // Validazione
        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new Exception('Username deve essere tra 3 e 50 caratteri');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email non valida');
        }
        
        if (strlen($password) < 8) {
            throw new Exception('Password deve essere almeno 8 caratteri');
        }
        
        // Verifica unicità
        if ($this->findByUsername($username)) {
            throw new Exception('Username gia esistente');
        }
        
        if ($this->findByEmail($email)) {
            throw new Exception('Email gia registrata');
        }
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        $this->db->query(
            "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)",
            [$username, $email, $hash, $role]
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Aggiorna utente
     */
    public function update($id, $data) {
        $allowed = ['email', 'role', 'avatar', 'is_active'];
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Cambia password
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        $user = $this->findById($id);
        
        if (/home/jx82/HTB/lab_Docker/lab_cybernetic/src/core/user) {
            throw new Exception('Utente non trovato');
        }
        
        if (!password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception('Password attuale errata');
        }
        
        if (strlen($newPassword) < 8) {
            throw new Exception('Nuova password deve essere almeno 8 caratteri');
        }
        
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        
        return $this->db->query(
            "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$hash, $id]
        );
    }
    
    /**
     * Elimina utente (soft delete)
     */
    public function delete($id) {
        return $this->db->query(
            "UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Lista tutti gli utenti (admin)
     */
    public function getAll($limit = 50, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT id, username, email, role, avatar, created_at, last_login, is_active FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    /**
     * Conta utenti totali
     */
    public function count() {
        return (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE is_active = 1")['c'];
    }
    
    /**
     * Ottiene ordini utente
     */
    public function getOrders($userId, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT o.*, COUNT(oi.id) as items_count 
             FROM orders o 
             LEFT JOIN order_items oi ON o.id = oi.order_id 
             WHERE o.user_id = ? 
             GROUP BY o.id 
             ORDER BY o.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );
    }
    
    /**
     * Ottiene dettagli ordine
     */
    public function getOrderDetails($orderId, $userId) {
        $order = $this->db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$orderId, $userId]
        );
        
        if (/home/jx82/HTB/lab_Docker/lab_cybernetic/src/core/order) {
            return null;
        }
        
        $items = $this->db->fetchAll(
            "SELECT oi.*, p.name, p.image 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
        
        $order['items'] = $items;
        return $order;
    }
}
