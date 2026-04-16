-- Create and Use Database
CREATE DATABASE IF NOT EXISTS homs_db;
USE homs_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Receptionist', 'Customer') NOT NULL DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Guest Records (Extended User Info)
CREATE TABLE IF NOT EXISTS guests (
    guest_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    id_proof_type VARCHAR(50), 
    id_proof_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_type ENUM('Single', 'Deluxe', 'Suite') NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    status ENUM('Available', 'Occupied', 'Maintenance') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
);

-- 5. Payments Table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('Cash', 'Card', 'UPI') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- DUMMY DATA INSERTION --

-- Insert Default Users (Password is 'password123' for all)
-- password_hash('password123', PASSWORD_DEFAULT) gives an equivalent hash. I will use a pre-generated hash for 'password123' here.
INSERT IGNORE INTO users (user_id, username, password_hash, role) VALUES 
(1, 'admin', '$2y$10$wT3yTo/A8Q9102uY4tXjHeEYi6a8T3Pz/f71gMlyfM1DkKzBHzIOW', 'Admin'),
(2, 'receptionist', '$2y$10$wT3yTo/A8Q9102uY4tXjHeEYi6a8T3Pz/f71gMlyfM1DkKzBHzIOW', 'Receptionist'),
(3, 'johndoe', '$2y$10$wT3yTo/A8Q9102uY4tXjHeEYi6a8T3Pz/f71gMlyfM1DkKzBHzIOW', 'Customer'),
(4, 'janedoe', '$2y$10$wT3yTo/A8Q9102uY4tXjHeEYi6a8T3Pz/f71gMlyfM1DkKzBHzIOW', 'Customer');

-- Insert Guest Profiles for Customers
INSERT IGNORE INTO guests (user_id, full_name, email, phone, id_proof_type, id_proof_number) VALUES
(3, 'John Doe', 'john@example.com', '1234567890', 'Passport', 'A1234567'),
(4, 'Jane Doe', 'jane@example.com', '0987654321', 'Driver License', 'D9876543');

-- Insert Dummy Rooms
INSERT IGNORE INTO rooms (room_id, room_type, price_per_night, status) VALUES
(101, 'Single', 50.00, 'Available'),
(102, 'Single', 50.00, 'Occupied'),
(201, 'Deluxe', 100.00, 'Available'),
(202, 'Deluxe', 100.00, 'Maintenance'),
(301, 'Suite', 250.00, 'Available');

-- Insert Dummy Bookings
INSERT IGNORE INTO bookings (booking_id, user_id, room_id, check_in, check_out, total_amount, status) VALUES
(1, 3, 102, CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 2 DAY, 150.00, 'Confirmed'),
(2, 4, 201, CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 7 DAY, 200.00, 'Pending');

-- Insert Dummy Payments
INSERT IGNORE INTO payments (payment_id, booking_id, amount, payment_method, payment_status) VALUES
(1, 1, 150.00, 'Card', 'Completed'),
(2, 2, 200.00, 'UPI', 'Pending');
