CREATE DATABASE IF NOT EXISTS errorlab;
USE errorlab;
CREATE TABLE reports(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(128),body TEXT);
CREATE TABLE secrets(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(64),value VARCHAR(128));
INSERT INTO reports(title,body) VALUES ('Status','Legacy reporting endpoint retained.');
INSERT INTO secrets(name,value) VALUES ('taylor','1b494afb39dd417ce4a12fb8cd962a2861d561e76cb711857ef99574583e08cc');
