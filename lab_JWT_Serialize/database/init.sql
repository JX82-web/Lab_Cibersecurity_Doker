CREATE DATABASE IF NOT EXISTS cyber_site;
USE cyber_site;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users (username, password)
VALUES ('portal', 'portal_lab_2026')
ON DUPLICATE KEY UPDATE password = VALUES(password);
