CREATE DATABASE IF NOT EXISTS unionlab;
USE unionlab;

-- 1. CREAZIONE TABELLE
CREATE TABLE IF NOT EXISTS store_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    cost DECIMAL(10,2),
    notes TEXT
);

CREATE TABLE IF NOT EXISTS internal_secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secret_key VARCHAR(100),
    value VARCHAR(255)
);

-- 2. POPOLAMENTO DATI
INSERT INTO store_items (item_name, category, cost, notes) VALUES
('Filtro SQL Avanzato', 'Cybersecurity', 150.00, 'Modulo didattico per eliminare i caratteri nocivi.'),
('WAF Hardware v2', 'Network', 2400.00, 'Protezione per attacchi applicativi di livello 7.'),
('Licenza Pentest Tool', 'Software', 89.99, 'Licenza di 30 giorni per testing interno.');

INSERT INTO internal_secrets (secret_key, value) VALUES
('cron_backup_path', '/var/www/html/backup_tasks'),
('internal_pwn_port', '127.0.0.1:8080');

-- 3. PRIVILEGI DI SISTEMA (Essenziale per aggirare l'errore 1227)
GRANT FILE ON *.* TO 'web_user'@'%';
FLUSH PRIVILEGES;
