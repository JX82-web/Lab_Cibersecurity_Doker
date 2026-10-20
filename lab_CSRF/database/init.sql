-- ========================================================
-- DATABASE APPLICAZIONE WEB (CSRF & SQLi LAB)
-- ========================================================
CREATE DATABASE IF NOT EXISTS csrf_db;
USE csrf_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    body TEXT NOT NULL
);

-- Inserimento utenti web con password in chiaro per scopi didattici
INSERT INTO users (username, password, role) VALUES
('admin', 'lovesucks1', 'admin'),
('analyst', 'analyst@123', 'user')
ON DUPLICATE KEY UPDATE username=username;

-- Inserimento note di audit
INSERT INTO notes (title, body) VALUES
('Deployment note', 'The legacy authentication module still concatenates login input into a SQL query.'),
('Operations note', 'The admin backup page contains an old operational export used during server migrations.');

-- ========================================================
-- DATABASE DEL SISTEMA (SOSTITUISCE IL DB 'MYSQL' INTERNO)
-- ========================================================
CREATE DATABASE IF NOT EXISTS system_db;
USE system_db;

CREATE TABLE IF NOT EXISTS system_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'user'
);

-- Inserimento utenti di sistema/credenziali SSH
INSERT INTO system_users (username, password, role) VALUES
('root', 'lovesucks1', 'admin'),
('iury', 'johnsucks1', 'user')
ON DUPLICATE KEY UPDATE username=username;
