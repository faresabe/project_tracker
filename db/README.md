# Database Setup Instructions

## 📋 Prerequisites
- MySQL Server (5.7 or higher)
- phpMyAdmin or MySQL command line access

## 🚀 Setup Steps

### Method 1: Using phpMyAdmin
1. Open phpMyAdmin in your browser (`http://localhost/phpmyadmin`)
2. Click on "Import" tab
3. Choose the `setup_database.sql` file
4. Click "Go" to execute

### Method 2: Using MySQL Command Line
```bash
mysql -u root -p < db/setup_database.sql
```

### Method 3: Manual Setup
1. Open phpMyAdmin or MySQL Workbench
2. Create a new database named `project_manager`
3. Copy and paste the SQL from `setup_database.sql`
4. Execute the queries

## 🗂️ Database Structure

### Tables Created:

#### `users` table
- `id` - Primary key (AUTO_INCREMENT)
- `first_name` - User's first name
- `last_name` - User's last name  
- `email` - Unique email address
- `password_hash` - Encrypted password
- `created_at` - Registration timestamp
- `updated_at` - Last update timestamp

#### `projects` table
- `id` - Primary key (AUTO_INCREMENT)
- `user_id` - Foreign key to users table
- `title` - Project title
- `type` - Project type (web, mobile, design, other)
- `description` - Project description
- `start_date` - Project start date
- `end_date` - Project end date
- `status` - Project status (pending, ongoing, completed)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `tasks` table
- `id` - Primary key (AUTO_INCREMENT)
- `project_id` - Foreign key to projects table
- `name` - Task name
- `type` - Task type (feature, bug, improvement)
- `description` - Task description
- `start_date` - Task start date
- `end_date` - Task end date
- `status` - Task status (pending, ongoing, done)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## 🔐 Security Features
- Foreign key constraints for data integrity
- Indexes for improved query performance
- Password hashing using PHP's `password_hash()`
- Cascade delete for related records

## 📊 Sample Data
The setup includes sample users, projects, and tasks for testing purposes.

**Sample Login Credentials:**
- Email: `john@example.com`
- Password: `password` (hashed in database)

- Email: `jane@example.com`  
- Password: `password` (hashed in database)

## 🔧 Configuration
Make sure your `authentication/db_connection.php` matches these settings:

```php
$host = 'localhost';
$dbname = 'project_manager';
$username = 'root';        // Your MySQL username
$password = '';            // Your MySQL password
```

## ✅ Verification
After setup, you should see:
- 3 tables created (users, projects, tasks)
- Sample data inserted
- All foreign key relationships established
- Indexes created for performance

## 🚨 Troubleshooting
- Ensure MySQL server is running
- Check database credentials in `db_connection.php`
- Verify user has CREATE and INSERT privileges
- Make sure database name matches in connection file