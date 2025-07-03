# 📌 Project Management Web App

A simple, full-stack Project Management app with **authentication** — including user **sign up**, **sign in**, **logout**, and a **personal dashboard** for managing projects and tasks.

---

## 🚀 Features

✅ Landing page (`index.html`)  
✅ User registration (sign up)  
✅ User login (sign in)  
✅ User session management (PHP Sessions)  
✅ Dashboard with sidebar navigation  
✅ Add, edit, delete projects & tasks  
✅ Logout redirects user back to landing page

---


---

## ⚙️ How It Works

### 1️⃣ Landing Page
- `index.html` is the public page.
- Links to **Sign Up** and **Sign In**.

---

### 2️⃣ Authentication

- `sign_up.html` → POSTs to `auth_php/register.php` (new user).
- `sign_in.html` → POSTs to `auth_php/login.php` (start session).
- `check_auth.php` → Checks active session.
- `logout.php` → Destroys session.

All handled by `auth.js`.

---

### 3️⃣ Dashboard

- After login, user is redirected to `dashboard/board.html`.
- Sidebar loads partial pages with `fetch`.
- Projects saved/updated via `php_api/` backend.
- `Logout` link calls `logout.php` → destroys session → redirects to `../index.html`.

---

## ✅ Technologies

- **Frontend:** HTML, CSS, JavaScript 
- **Backend:** PHP 
- **Database:** MySQL

---

## ⚡️ How to Run

1. ✅ Place project in your **local web server** (e.g., `htdocs` for XAMPP).
2. ✅ Create a **MySQL database** (`project_manager`).
3. ✅ Add a `users` table:
   ```sql
   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     first_name VARCHAR(50) NOT NULL,
     last_name VARCHAR(50) NOT NULL,
     email VARCHAR(100) NOT NULL UNIQUE,
     password_hash VARCHAR(255) NOT NULL
   );


