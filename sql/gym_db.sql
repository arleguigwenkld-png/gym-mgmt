CREATE DATABASE gym_mgmt;

USE gym_mgmt;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('front_desk') NOT NULL
);

CREATE TABLE pending_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(10) NOT NULL UNIQUE,
    family_name VARCHAR(50) NOT NULL,
    given_name VARCHAR(50) NOT NULL,
    birthday DATE,
    contact_number VARCHAR(20),
    membership_availed VARCHAR(50) NOT NULL,
    membership_rate DECIMAL(10,2) NOT NULL,
    payment_mode VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    membership_status VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL
);

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(10) NOT NULL UNIQUE,
    family_name VARCHAR(50) NOT NULL,
    given_name VARCHAR(50) NOT NULL,
    birthday DATE,
    contact_number VARCHAR(20),
    membership_availed VARCHAR(50) NOT NULL,
    membership_rate DECIMAL(10,2) NOT NULL,
    payment_mode VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    sales_person VARCHAR(50),
    activation_date DATE,
    expiry_date DATE,
    membership_status VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL
);
