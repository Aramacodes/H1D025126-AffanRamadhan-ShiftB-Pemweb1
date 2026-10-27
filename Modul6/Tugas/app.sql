CREATE DATABASE db_cod;
USE db_cod;

CREATE TABLE barang (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    harga INT(11) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE pembeli (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    hp VARCHAR(20) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE kurir (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    hp VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO barang (id, nama, harga) VALUES
(1, 'Headset', 75000),
(2, 'Keyboard', 125000),
(3, 'Mouse', 50000);

INSERT INTO pembeli (id, nama, hp, alamat) VALUES
(1, 'Andi', '081234567801', 'Purwokerto'),
(2, 'Budi', '081234567802', 'Purbalingga'),
(3, 'Citra', '081234567803', 'Banyumas');

INSERT INTO kurir (id, nama, hp) VALUES
(1, 'Doni', '081234567811'),
(2, 'Eko', '081234567812'),
(3, 'Fajar', '081234567813');