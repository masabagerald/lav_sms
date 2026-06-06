# Laravel School Management System (LAVSMS)

LAVSMS is a comprehensive School Management System built with Laravel 8 for schools, colleges, and other educational institutions. The system provides role-based access control, student information management, examination management, fee tracking, library management, and academic reporting.

## Features

### Dashboard

<img src="https://i.ibb.co/D4T0z6T/dashboard.png" alt="Dashboard">

### Login

<img src="https://i.ibb.co/Rh1Bfwk/login.png" alt="Login">

### Student Marksheet

<img src="https://i.ibb.co/GCgv5ZR/marksheet.png" alt="Student Marksheet">

### System Settings

<img src="https://i.ibb.co/Kmrhw69/system-settings.png" alt="System Settings">

### Print Marksheet

<img src="https://i.ibb.co/5c1GHCj/capture-20210530-115521-crop.png" alt="Print Marksheet">

### Print Tabulation Sheet

<img src="https://i.ibb.co/QmscPfn/capture-20210530-115802.png" alt="Tabulation Sheet">

---

## User Roles

The system supports seven user roles:

* Super Administrator
* Administrator
* Teacher
* Student
* Parent
* Accountant
* Librarian

---

## System Requirements

### Traditional Installation

* PHP 7.4 or later
* Composer
* MySQL / PostgreSQL
* Node.js & NPM (optional for frontend assets)

### Docker Installation

* Docker
* Docker Compose

---

# Quick Start with Docker

## 1. Clone the Repository

```bash
git clone https://github.com/yourusername/lavsms.git
cd lavsms
```

## 2. Configure Environment

```bash
cp .env.example .env
```

Update database settings in `.env`.

## 3. Build and Start Containers

```bash
docker compose up --build -d
```

## 4. Install Dependencies

```bash
docker compose exec app composer install
```

## 5. Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

## 6. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

## 7. Seed Demo Data

```bash
docker compose exec app php artisan db:seed
```

## 8. Fix Storage Permissions

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## 9. Access the Application

```text
http://localhost:8000
```

---

# Manual Installation

## Install Dependencies

```bash
composer install
```

## Configure Environment

```bash
cp .env.example .env
```

Update your database credentials in the `.env` file.

## Generate Application Key

```bash
php artisan key:generate
```

## Run Migrations

```bash
php artisan migrate
```

## Seed Database

```bash
php artisan db:seed
```

## Start Development Server

```bash
php artisan serve
```

---

# Demo Login Credentials

After running the database seeders, use the following credentials:

| Role        | Username   | Email                                                         | Password |
| ----------- | ---------- | ------------------------------------------------------------- | -------- |
| Super Admin | cj         | [cj@cj.com](mailto:cj@cj.com)                                 | cj       |
| Admin       | admin      | [admin@admin.com](mailto:admin@admin.com)                     | cj       |
| Teacher     | teacher    | [teacher@teacher.com](mailto:teacher@teacher.com)             | cj       |
| Parent      | parent     | [parent@parent.com](mailto:parent@parent.com)                 | cj       |
| Accountant  | accountant | [accountant@accountant.com](mailto:accountant@accountant.com) | cj       |
| Student     | student    | [student@student.com](mailto:student@student.com)             | cj       |

---

# Role Capabilities

## Super Administrator

* Create all user accounts
* Manage all system settings
* Delete any record in the system

## Administrators

* Manage students, classes, and sections
* Manage examinations and grading
* Manage subjects
* Manage user accounts
* Manage school notices and events
* Manage fee structures and payments

## Teachers

* Manage assigned classes and subjects
* Enter and update student results
* Manage timetables
* Upload learning materials
* Update personal profile

## Students

* View academic results
* Access class timetable
* View fee payment status
* Access learning materials
* Manage personal profile

## Parents

* Monitor student performance
* View and print report cards
* View fee payment records
* Track class timetable
* Manage personal profile

## Accountants

* Manage fees and payments
* Generate payment receipts
* Track financial transactions

## Librarians

* Manage library inventory
* Issue and receive books
* Track borrowing history

---

# Contributing

Contributions, feature requests, and bug reports are welcome.

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Submit a Pull Request

---

# Security

If you discover a security vulnerability, please contact the maintainer directly instead of creating a public issue.

---

# Roadmap

The following modules are currently being improved:

* Noticeboard and Calendar
* Library Management
* Study Materials Management
* Accountant Dashboard
* Librarian Dashboard

---

# License

This project is open-source and available under the MIT License.

---

# Author

