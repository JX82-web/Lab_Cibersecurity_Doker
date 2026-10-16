CREATE DATABASE IF NOT EXISTS lab_db;
USE lab_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(64) NOT NULL
);

INSERT INTO users (username, password) VALUES ('richard', 'colinasdeplata801');

CREATE TABLE IF NOT EXISTS tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_code VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL
);

INSERT INTO tracking (status_code, description) VALUES ('TRACK-01', 'Initial deployment successful');
INSERT INTO tracking (status_code, description) VALUES ('TRACK-02', 'System backup completed');
