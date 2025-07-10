# 🚀 Project tracker Web App

A simple, beginner-friendly project management web application built with **HTML**, **CSS**, **JavaScript**, and **PHP**.  
Users can **register**, **log in**,  and track their own **projects** and **tasks**.

---

## 🎯 **Features**

✅ User authentication (Sign Up, Sign In, Logout)   
✅ Dashboard with dynamic sidebar navigation  
✅ Add, edit, and delete projects  
✅ Add, edit, and delete tasks inside projects   
✅ Secure backend with PHP & MySQL

---

## 🗂️ **Screenshots**
### 📌 Landing Page
![Group 1000004353](https://github.com/user-attachments/assets/f3702b5b-4f14-480d-820e-6cca2175c2dc)

### 📌 Dashboard
![dashboard 2](https://github.com/user-attachments/assets/3e4621a2-227c-4cdc-8e0e-885164c115db)



# 🚀 Project tracker — Setup & Run Guide

## 📌 What it does

✅ Users can **sign up**, **sign in**, **log out**,  
✅ Authenticated users see their **Dashboard**  
✅ Users can **create projects**, **add tasks**, and manage them in a simple UI

---

## ⚙️ Requirements

- **Local server:** XAMPP / MAMP / WAMP / LAMP  
- **MySQL**  
- A modern browser (Chrome, Firefox, Edge)

---

## 🗂️ Step 1 — Clone or Copy

Place the project folder (e.g., `project_manager/`) inside your local server’s root directory:

- XAMPP: `htdocs/`
- MAMP: `htdocs/`
- WAMP: `www/`
- LAMP: `/var/www/html/`

---

## 🗂️ Step 2 — Setup Database

1️⃣ Open **phpMyAdmin** at `http://localhost/phpmyadmin`  
2️⃣ Click **New** → **Create database** → Name it: 
3️⃣ Click the **Import** tab → Select:
4️⃣ Click **Go** → This creates:
- `users` table
- `projects` table
- `tasks` table

---
## 🗂️ Step 3 — Check Database Config
Open:
Make sure:
```php
$host = 'localhost';
$dbname = 'project_manager';
$username = 'root'; // your MySQL username
$password = '';     // your MySQL password
---
🗂️ Step 4 — Start the Server






