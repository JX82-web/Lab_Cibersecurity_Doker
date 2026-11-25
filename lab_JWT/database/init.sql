CREATE DATABASE IF NOT EXISTS lab_jwt_db;
USE lab_jwt_db;

DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user'
);

INSERT INTO users (username, password, role) VALUES
('guest', 'f05833449a89a965cf5a729d22cdf227cca97f7d780eb10eb4533affd37eddf5', 'user'),
('robert', 'b710148fb6cc494b9c06bd9672c59620bac1cf615d1f6c868d26689e78e3f484', 'user'),
('admin', '8fcf0cccf1eb395aba8af20083348eddf291238763952af1ca825e1551ab16de', 'admin');

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    content TEXT
);

INSERT INTO notes (title, content) VALUES 
('System Maintenance', 'All scheduled updates and database backups have been successfully archived by system operators.'),
('Security Bulletin', 'Ensure all legacy credentials adhere to internal password complexity requirements.');


