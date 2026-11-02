CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

CREATE TABLE IF NOT EXISTS web_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(64) NOT NULL,
    role VARCHAR(20) DEFAULT 'user'
);

-- Inserimento degli utenti aziendali reali
-- Password dell'admin: "admin_master_password_2026" -> MD5: "181222e628af11ca5a3cc8e452c4cf12"
-- Password di michael: "mypassword123!" -> MD5: "ae01666313dcf2dbd6de95598083f062"
INSERT IGNORE INTO web_users (username, email, password_hash, role) VALUES
('admin', 'admin@shopcore.htb', '181222e628af11ca5a3cc8e452c4cf12', 'administrator'),
('michael', 'michael@shopcore.htb', 'ae01666313dcf2dbd6de95598083f062', 'developer');
