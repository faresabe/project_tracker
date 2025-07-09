-- Project Manager Database Setup
-- MySQL Database Structure

-- Create database
CREATE DATABASE IF NOT EXISTS project_manager;
USE project_manager;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(100) DEFAULT 'web',
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'ongoing', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('feature', 'bug', 'improvement') DEFAULT 'feature',
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'ongoing', 'done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_projects_user_id ON projects(user_id);
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_tasks_project_id ON tasks(project_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_users_email ON users(email);

-- Insert sample data (optional)
INSERT INTO users (first_name, last_name, email, password_hash) VALUES
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO projects (user_id, title, type, description, start_date, end_date, status) VALUES
(1, 'E-commerce Website', 'web', 'Building a modern e-commerce platform with React and Node.js', '2024-01-15', '2024-03-15', 'ongoing'),
(1, 'Mobile App Development', 'mobile', 'Creating a cross-platform mobile app using React Native', '2024-02-01', '2024-04-01', 'pending'),
(2, 'UI/UX Redesign', 'design', 'Redesigning the company website for better user experience', '2024-01-10', '2024-02-28', 'completed');

INSERT INTO tasks (project_id, name, type, description, start_date, end_date, status) VALUES
(1, 'Setup Database Schema', 'feature', 'Create and configure the database structure', '2024-01-15', '2024-01-20', 'done'),
(1, 'User Authentication', 'feature', 'Implement login and registration functionality', '2024-01-21', '2024-01-30', 'ongoing'),
(1, 'Fix Login Bug', 'bug', 'Resolve issue with password validation', '2024-01-25', '2024-01-26', 'pending'),
(2, 'Design Wireframes', 'feature', 'Create initial app wireframes and mockups', '2024-02-01', '2024-02-10', 'pending'),
(3, 'User Research', 'improvement', 'Conduct user interviews and surveys', '2024-01-10', '2024-01-20', 'done');