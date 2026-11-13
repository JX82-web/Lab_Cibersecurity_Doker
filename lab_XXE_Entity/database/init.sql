CREATE DATABASE IF NOT EXISTS shop;
USE shop;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'customer'
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, description, price, image) VALUES
('Aurora Laptop', 'Developer laptop for the Northbridge lab.', 899.00, 'laptop.svg'),
('Cipher Keyboard', 'Mechanical keyboard with programmable layers.', 129.00, 'keyboard.svg'),
('Packet Analyzer', 'Training appliance for defensive network analysis.', 249.00, 'analyzer.svg'),
('Red Team Mug', 'Ceramic mug for long lab sessions.', 18.00, 'mug.svg');
