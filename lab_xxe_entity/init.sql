CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user'
);
INSERT INTO users (username, password_hash, role) VALUES
('jack', '39c61305c0ed4b8b35f73614d77091d2c753707cb3cb9231eab2d656a9968d34', 'user'),
('admin', 'cab72158a7a8cfb7d1b5d7770cf607cca44c595a71cef70e2ee89dba7bae2501', 'admin');
