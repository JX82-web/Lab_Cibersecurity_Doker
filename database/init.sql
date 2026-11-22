create database if not exists ecommerce;
use ecommerce;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(64) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
  total_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

insert into products (name, description, price, image) values
('Amd Laptop', 'Developer laptop for the Northbridge lab.', 899.00, 'laptop.svg'),
('Keyboard', 'Mechanical keyboard with programmable layers.', 129.00, 'keyboard.svg'),
('Analyzer', 'Training appliance for defensive network analysis.', 249.00, 'analyzer.svg'),
('Mug', 'Ceramic mug for long lab sessions.', 18.00, 'mug.svg');

insert into users (username, email, password_hash, role) values
('admin','admin@ecommerce.io','2af5a9a28584579d7cf5053e8a0faff78e7b9e1de8a48ece6a664f6254bdaddb','admin'),
('jeffry','jeffry@ecommerce.io','b30e45bc91d620e63cb8fd1dc08772b02d73718c7583a349705cc54c2d3fefd9','user');

-- Seed data: give the IDOR a visible target and keep jeffry as the only non-admin SSH user.
INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES
(1, 3, 1, 249.00),
(2, 1, 1, 899.00);
