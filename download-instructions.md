# Project Download Instructions

## How to Download Your Complete Project

Since this is a web-based environment, here are the best ways to get all your project files:

### Method 1: Manual Download (Recommended)
1. **Right-click** on each file in the file explorer (left sidebar)
2. Select **"Download"** or **"Save As"**
3. Maintain the folder structure as shown below

### Method 2: Copy File Contents
1. Click on each file to open it
2. Select all content (Ctrl+A / Cmd+A)
3. Copy and paste into new files on your local machine

## Required Folder Structure

Create this exact folder structure on your local machine:

```
project_manager/
├── index.html
├── style.css
├── README.md
├── Assets/
│   ├── Portfolio-dashboard-compressed.png
│   ├── what-is-a-task-tracker-and-why-you-need-one.jpeg
│   └── (other image files)
├── authentication/
│   ├── config.php
│   ├── db_connection.php
│   ├── sign_in.php
│   ├── sign_up.php
│   └── auth_php/
│       ├── check_auth.php
│       ├── login.php
│       ├── logout.php
│       └── register.php
├── dashboard/
│   ├── board.php
│   ├── board.css
│   ├── partials/
│   │   ├── home.php
│   │   ├── add_project.php
│   │   ├── project_details.php
│   │   └── settings.php
│   └── php_api/
│       ├── delete_project.php
│       ├── delete_task.php
│       ├── get_project_details.php
│       ├── get_projects.php
│       ├── logout.php
│       ├── save_project.php
│       ├── save_task.php
│       ├── update_project.php
│       └── update_project_status.php
└── db/
    ├── project_manager.sql
    └── README.md
```

## Setup Instructions After Download

1. **Place in Web Server Directory:**
   - XAMPP: `htdocs/project_manager/`
   - MAMP: `htdocs/project_manager/`
   - WAMP: `www/project_manager/`

2. **Import Database:**
   - Open phpMyAdmin
   - Create database named `project_manager`
   - Import `db/project_manager.sql`

3. **Configure Database Connection:**
   - Edit `authentication/db_connection.php`
   - Update MySQL credentials if needed

4. **Access Application:**
   - Visit: `http://localhost/project_manager/`

## Important Files to Download

### Core Files:
- `index.html` - Landing page
- `style.css` - Main stylesheet

### Authentication:
- `authentication/sign_in.php` - Login page
- `authentication/sign_up.php` - Registration page
- `authentication/config.php` - Auth configuration
- `authentication/db_connection.php` - Database connection

### Dashboard:
- `dashboard/board.php` - Main dashboard
- `dashboard/board.css` - Dashboard styles
- All files in `dashboard/partials/` - Page components
- All files in `dashboard/php_api/` - Backend API

### Database:
- `db/project_manager.sql` - Database structure and data

## Testing the Application

1. Start your local server (XAMPP/MAMP/WAMP)
2. Import the database
3. Visit the landing page
4. Test registration and login
5. Create projects and tasks

## Need Help?

If you encounter issues:
1. Check database connection settings
2. Ensure all files are in correct folders
3. Verify MySQL server is running
4. Check PHP error logs