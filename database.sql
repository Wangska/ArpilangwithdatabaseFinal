-- SplitWise Database Structure
CREATE DATABASE IF NOT EXISTS splitwise_db;
USE splitwise_db;

-- Users table for authentication and profile management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    nickname VARCHAR(50),
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table for bill categorization
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'default',
    color VARCHAR(7) DEFAULT '#6366f1',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bills table for storing bill information
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    total_amount DECIMAL(10, 2) DEFAULT 0.00,
    category_id INT,
    creator_id INT NOT NULL,
    invitation_code VARCHAR(10) UNIQUE,
    status ENUM('active', 'settled', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bill participants table for linking users to bills
CREATE TABLE bill_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (bill_id, user_id)
);

-- Expenses table for individual expense items within bills
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    paid_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Expense splits table for tracking how expenses are split among participants
CREATE TABLE expense_splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, icon, color, is_default) VALUES
('Food & Dining', '🍽️', '#ef4444', TRUE),
('Transportation', '🚗', '#f97316', TRUE),
('Entertainment', '🎬', '#8b5cf6', TRUE),
('Shopping', '🛍️', '#06b6d4', TRUE),
('Utilities', '💡', '#eab308', TRUE);

-- Insert sample users (passwords are hashed using PHP password_hash())
INSERT INTO users (first_name, last_name, nickname, email, username, password) VALUES
('John', 'Doe', 'Johnny', 'john@example.com', 'johndoe123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('Jane', 'Smith', 'Janie', 'jane@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert sample bills
INSERT INTO bills (name, total_amount, category_id, creator_id, invitation_code, status) VALUES
('Weekend Trip to Mountains', 320.50, 2, 1, 'ABC123XYZ', 'active'),
('Grocery Shopping', 156.75, 4, 1, 'GRO456DEF', 'active'),
('Dinner at Restaurant', 85.20, 1, 2, 'DIN789GHI', 'settled');

-- Insert bill participants
INSERT INTO bill_participants (bill_id, user_id) VALUES
(1, 1), (1, 2),  -- Both users in Weekend Trip
(2, 1), (2, 2),  -- Both users in Grocery Shopping
(3, 1), (3, 2);  -- Both users in Dinner (archived)

-- Insert sample expenses
INSERT INTO expenses (bill_id, description, amount, paid_by) VALUES
(1, 'Hotel Accommodation', 200.00, 1),
(1, 'Gas for Car', 80.50, 2),
(1, 'Hiking Gear', 40.00, 1),
(2, 'Weekly Groceries', 120.00, 1),
(2, 'Household Items', 36.75, 2),
(3, 'Restaurant Bill', 85.20, 2);

-- Insert expense splits (equal splits for simplicity)
INSERT INTO expense_splits (expense_id, user_id, amount) VALUES
-- Weekend Trip expenses (split equally between 2 people)
(1, 1, 100.00), (1, 2, 100.00),  -- Hotel
(2, 1, 40.25), (2, 2, 40.25),    -- Gas
(3, 1, 20.00), (3, 2, 20.00),    -- Hiking Gear
-- Grocery expenses
(4, 1, 60.00), (4, 2, 60.00),    -- Weekly Groceries
(5, 1, 18.38), (5, 2, 18.37),    -- Household Items
-- Restaurant expense
(6, 1, 42.60), (6, 2, 42.60);    -- Restaurant Bill
