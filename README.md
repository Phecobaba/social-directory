# Social Club Member Directory System

## 📌 Project Overview

This is an admin-only web application for managing members of a social club.

The system allows administrators to:

* Add, edit, delete, and view members
* Upload optional member photos
* Add dynamic/custom fields per member
* Search, filter, and paginate members
* Import members via CSV
* Export members to CSV/Excel

---

## 🛠️ Tech Stack

* Laravel 11
* MySQL
* Blade Templates
* AdminLTE (Bootstrap-based UI)

---

## 🔐 Authentication

* Admin-only access
* Login with email or username
* No public registration

---

## 🧱 Core Modules

### 1. Dashboard

* Displays total members
* Shows recently added members

### 2. Member Management

* CRUD operations
* Profile view page
* Dynamic custom fields (JSON)

### 3. Search & Pagination

* Search by name and phone
* Paginated results

### 4. Import/Export

* CSV import
* CSV/Excel export

---

## 📁 File Storage

* Member images stored in: storage/app/public
* Use: php artisan storage:link

---

## 🔐 Security

* CSRF protection
* Input validation (Form Requests)
* Password hashing (bcrypt)

---

## 📊 Database Schema

### members table:

* id
* full_name
* phone_number
* address
* photo (nullable)
* dynamic_fields (JSON, nullable)
* timestamps

---

## ⚙️ Setup Instructions

1. Clone repo
2. Run: composer install
3. Copy .env and configure database
4. Run: php artisan migrate
5. Run: php artisan serve

---

## 🧠 Development Rules

* Follow MVC structure strictly
* Use resource controllers
* Validate all inputs
* Keep UI responsive
* Write clean, modular code

---

## 🚧 Current Status

See TASKS.md for progress tracking.

---

## 🤖 Agent Instructions

Before making any changes:

1. Read README.md
2. Read TASKS.md
3. Continue from the last completed task
4. Do NOT rewrite existing working code unless necessary
5. Maintain consistency with Laravel best practices

---
