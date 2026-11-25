<?php
/**
 * Database Connection Wrapper
 * Supporta SQLite e MySQL via PDO
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $config;
    
    private function __construct() {
        $this->loadConfig();
        $this->connect();
        $this->initSchema();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        $configPath = __DIR__ . '/../../config/app_config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = [
                'db_type' => 'sqlite',
                'db_path' => '/var/www/html/data/cybernetic.db',
                'debug_mode' => true
            ];
        }
    }
    
    private function connect() {
        try {
            if ($this->config['db_type'] === 'sqlite') {
                $dbDir = dirname($this->config['db_path']);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                $dsn = "sqlite:{$this->config['db_path']}";
                $this->pdo = new PDO($dsn);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } else {
                // MySQL support
                $dsn = "mysql:host={$this->config['db_host']};dbname={$this->config['db_name']};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->config['db_user'], $this->config['db_pass']);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            if ($this->config['debug_mode']) {
                die("Errore connessione DB: " . $e->getMessage());
            }
            die("Errore connessione database");
        }
    }
    
    private function initSchema() {
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            avatar VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME DEFAULT NULL,
            is_active BOOLEAN DEFAULT 1
        );
        
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(50),
            image VARCHAR(255),
            stock INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        );
        
        CREATE TABLE IF NOT EXISTS audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action VARCHAR(50) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(64) PRIMARY KEY,
            user_id INTEGER NOT NULL,
            data TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        ";
        
        try {
            $this->pdo->exec($schema);
            $this->seedData();
        } catch (PDOException $e) {
            if ($this->config['debug_mode']) {
                error_log("Schema init error: " . $e->getMessage());
            }
        }
    }
    
    private function seedData() {
        // Verifica se utenti esistono
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        
        // Utenti di default
        $users = [
            ['admin', 'admin@cybernetic.local', password_hash('admin123', PASSWORD_BCRYPT), 'admin', null],
            ['user', 'user@cybernetic.local', password_hash('user123', PASSWORD_BCRYPT), 'user', null],
            ['premium', 'premium@cybernetic.local', password_hash('premium123', PASSWORD_BCRYPT), 'premium', null]
        ];
        
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role, avatar) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute($u);
        }
        
        // Prodotti di esempio
        $products = [
            ['Laptop Gaming Pro', 'Potente laptop per gaming', 1299.99, 'elettronica', 'laptop.jpg', 10],
            ['Mouse Wireless', 'Mouse ergonomico wireless', 29.99, 'accessori', 'mouse.jpg', 50],
            ['Tastiera Meccanica', 'Tastiera RGB mechanical', 89.99, 'accessori', 'keyboard.jpg', 30],
            ['Monitor 27" 144Hz', 'Monitor gaming 27 pollici', 349.99, 'elettronica', 'monitor.jpg', 15],
            ['Licenza Windows Pro', 'Windows 11 Professional', 199.99, 'software', 'windows.jpg', 100],
            ['Antivirus Pro 1 Anno', 'Protezione completa', 39.99, 'software', 'antivirus.jpg', 200]
        ];
        
        $stmt = $this->pdo->prepare("INSERT INTO products (name, description, price, category, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
    }
    
    public function getPDO() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
