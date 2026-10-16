CREATE DATABASE IF NOT EXISTS authlab;
USE authlab;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'user'
);

CREATE TABLE audit_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    body TEXT NOT NULL
);

INSERT INTO users (username, password, role) VALUES
('admin', 'AdminPanel_2026!_ChangeMe', 'admin'),
('analyst', 'AnalystOnly_2026!', 'user');

INSERT INTO audit_notes (title, body) VALUES
('Deployment note', 'The legacy authentication module still concatenates login input into a SQL query.'),
('Operations note', 'The admin backup page contains an old operational export used during server migrations.');
