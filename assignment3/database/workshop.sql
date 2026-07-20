CREATE TABLE mechanics (
    mechanic_id INT AUTO_INCREMENT PRIMARY KEY,
    mechanic_name VARCHAR(100) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    car_license VARCHAR(50) NOT NULL,
    engine_number VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    mechanic_id INT NOT NULL,
    status ENUM('Booked','Completed','Cancelled') NOT NULL DEFAULT 'Booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_mechanic
        FOREIGN KEY (mechanic_id) REFERENCES mechanics(mechanic_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_date_mechanic (appointment_date, mechanic_id),
    INDEX idx_phone_date (phone, appointment_date)
);

INSERT INTO mechanics (mechanic_name) VALUES
('Rahim Ahmed'),
('Karim Hasan'),
('Tanvir Hossain'),
('Sakib Khan'),
('Imran Ali');

INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9k1QzN3mZ3V5JvRr8u');
