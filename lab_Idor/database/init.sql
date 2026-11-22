create database if not exists ecommerce;
use ecommerce;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(64) NOT NULL,
    role VARCHAR(20) DEFAULT 'user'
);

create table products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL
);

create table orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

insert into products (name, description, price, image) values
('Amd Laptop', 'Developer laptop for the Northbridge lab.', 899.00, 'laptop.svg'),
('Keyboard', 'Mechanical keyboard with programmable layers.', 129.00, 'keyboard.svg'),
('Analyzer', 'Training appliance for defensive network analysis.', 249.00, 'analyzer.svg'),
('Mug', 'Ceramic mug for long lab sessions.', 18.00, 'mug.svg');

insert into users (username, email, password_hash, role) values
('admin','admin@ecommerce.io','9beaece8a12d13c0106f3ce0753bab1977f658c167b3400f117041174f7dee92','admin'),
('jeffry','jeffry@ecommerce.io','e298dc5028b96c2822937a348f18f972be8f58666f2c1769949b5dfdc3332692','user');
