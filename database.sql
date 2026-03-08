-- Créer la base de données
CREATE DATABASE IF NOT EXISTS ambulancepro;
USE ambulancepro;

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('operateur', 'planning', 'chauffeur', 'facturation') NOT NULL,
    establishment_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des établissements de santé
CREATE TABLE establishments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    type ENUM('hopital', 'centre_soins', 'clinique') DEFAULT 'hopital',
    status ENUM('actif', 'inactif') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des véhicules
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration VARCHAR(20) UNIQUE NOT NULL,
    type ENUM('ambulance', 'vsl', 'autre') NOT NULL,
    capacity INT DEFAULT 1,
    status ENUM('disponible', 'occupé', 'maintenance') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des demandes de transport
CREATE TABLE transport_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    establishment_id INT NOT NULL,
    patient_name VARCHAR(255) NOT NULL,
    patient_phone VARCHAR(20),
    departure_address VARCHAR(255) NOT NULL,
    arrival_address VARCHAR(255) NOT NULL,
    transport_type ENUM('urgent', 'non_urgent') DEFAULT 'non_urgent',
    medical_info VARCHAR(500),
    special_equipment VARCHAR(255),
    status ENUM('crée', 'affecté', 'en_cours', 'terminé', 'annulé') DEFAULT 'crée',
    created_by INT NOT NULL,
    assigned_by INT,
    vehicle_id INT,
    driver1_id INT,
    driver2_id INT,
    departure_time DATETIME,
    arrival_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (driver1_id) REFERENCES users(id),
    FOREIGN KEY (driver2_id) REFERENCES users(id)
);

-- Table des factures
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    establishment_id INT NOT NULL,
    transport_request_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('en_attente', 'validée', 'payée') DEFAULT 'en_attente',
    validation_date DATETIME,
    validated_by INT,
    payment_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establishment_id) REFERENCES establishments(id),
    FOREIGN KEY (transport_request_id) REFERENCES transport_requests(id),
    FOREIGN KEY (validated_by) REFERENCES users(id)
);

-- Table d'audit pour les notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transport_request_id INT NOT NULL,
    message VARCHAR(500),
    type ENUM('création', 'affectation', 'départ', 'arrivée', 'facturation') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transport_request_id) REFERENCES transport_requests(id)
);

-- Insérer des établissements de test
INSERT INTO establishments (name, address, phone, email, type) VALUES
('Hôpital Central', '123 Rue de la Santé', '01-23-45-67-89', 'hopital@example.com', 'hopital'),
('Centre de Soins', '456 Avenue des Médecins', '01-98-76-54-32', 'centre@example.com', 'centre_soins'),
('Clinique Saint-Luc', '789 Boulevard de la Vie', '01-55-44-33-22', 'clinique@example.com', 'clinique');

-- Insérer des véhicules de test
INSERT INTO vehicles (registration, type, capacity) VALUES
('AM-001', 'ambulance', 2),
('AM-002', 'ambulance', 2),
('VSL-001', 'vsl', 4),
('VSL-002', 'vsl', 4),
('AM-003', 'ambulance', 2);

-- Insérer des utilisateurs de test
INSERT INTO users (email, password, name, role, establishment_id) VALUES
('operateur@example.com', '$2y$10$uIaIdRIvFJPd8s/h.FgLkuzAb7qQZ4KiFAUBrXfX9mU8Lw.JsQb7O', 'Jean Opérateur', 'operateur', 1),
('planning@example.com', '$2y$10$uIaIdRIvFJPd8s/h.FgLkuzAb7qQZ4KiFAUBrXfX9mU8Lw.JsQb7O', 'Marie Planning', 'planning', NULL),
('chauffeur1@example.com', '$2y$10$uIaIdRIvFJPd8s/h.FgLkuzAb7qQZ4KiFAUBrXfX9mU8Lw.JsQb7O', 'Pierre Chauffeur', 'chauffeur', NULL),
('chauffeur2@example.com', '$2y$10$uIaIdRIvFJPd8s/h.FgLkuzAb7qQZ4KiFAUBrXfX9mU8Lw.JsQb7O', 'Paul Chauffeur', 'chauffeur', NULL),
('facturation@example.com', '$2y$10$uIaIdRIvFJPd8s/h.FgLkuzAb7qQZ4KiFAUBrXfX9mU8Lw.JsQb7O', 'Sophie Facturation', 'facturation', NULL);

-- Les mots de passe test sont tous: 'password123'
