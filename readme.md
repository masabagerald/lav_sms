# 🎓 Laravel School Management System (LAVSMS)

![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php)
![Docker](https://img.shields.io/badge/Docker-Supported-2496ED?logo=docker)
![License](https://img.shields.io/badge/License-MIT-green)
![CI/CD](https://img.shields.io/badge/Jenkins-CI%2FCD-D24939?logo=jenkins)

A modern and comprehensive School Management System built with Laravel 8 for schools, colleges, and educational institutions.

LAVSMS simplifies academic administration through role-based access control, student management, examinations, fee tracking, library management, reporting, and school communication tools.

---

## ✨ Key Features

### Academic Management

* Student enrollment and management
* Class and section management
* Subject management
* Examination and grading system
* Marksheet generation and printing
* Tabulation sheet generation

### User Management

* Multi-role authentication and authorization
* User profile management
* Parent-student relationships
* Teacher assignments

### Financial Management

* Student fee management
* Payment tracking
* Receipt generation
* Financial reporting

### Library Management

* Book catalog management
* Book issuance and returns
* Borrowing history tracking

### Communication

* School noticeboard
* Academic announcements
* Calendar and event management

---

## 👥 Supported User Roles

| Role                | Description           |
| ------------------- | --------------------- |
| Super Administrator | Full system control   |
| Administrator       | School administration |
| Teacher             | Academic management   |
| Student             | Academic access       |
| Parent              | Student monitoring    |
| Accountant          | Financial management  |
| Librarian           | Library operations    |

---

## 🖼 Screenshots

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

### Tabulation Sheet

<img src="https://i.ibb.co/QmscPfn/capture-20210530-115802.png" alt="Tabulation Sheet">

---

## 🏗 System Architecture

```text
Browser
   │
   ▼
Laravel Application
   │
   ├── Authentication & Authorization
   ├── Academic Management
   ├── Financial Management
   ├── Library Management
   └── Reporting Module
   │
   ▼
MySQL / PostgreSQL
```

---

## 🚀 Quick Start (Docker Recommended)

### Clone Repository

```bash
git clone https://github.com/masabagerald/lav_sms.git
cd lav_sms
```

### Configure Environment

```bash
cp .env.example .env
```

Update database credentials and application settings.

### Start Containers

```bash
docker compose up --build -d
```

### Install Dependencies

```bash
docker compose exec app composer install
```

### Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### Seed Demo Data

```bash
docker compose exec app php artisan db:seed
```

### Fix Permissions

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Access Application

```text
http://localhost:8000
```

---

## 🔄 CI/CD with Jenkins

The project supports automated CI/CD pipelines using Jenkins and Docker.

Typical workflow:

```text
GitHub
   ↓
Jenkins Pipeline
   ↓
Build Docker Image
   ↓
Run Tests
   ↓
Deploy Application
```

Pipeline configuration is maintained using a Jenkinsfile (Pipeline as Code).

---

## 🔑 Demo Credentials

| Role        | Username   | Password |
| ----------- | ---------- | -------- |
| Super Admin | cj         | cj       |
| Admin       | admin      | cj       |
| Teacher     | teacher    | cj       |
| Parent      | parent     | cj       |
| Accountant  | accountant | cj       |
| Student     | student    | cj       |

---

## 🤝 Contributing

Contributions, suggestions, and pull requests are welcome.

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to your fork
5. Submit a Pull Request

---

## 🛡 Security

If you discover a security vulnerability, please create a private disclosure or contact the maintainer directly.

---

## 🗺 Roadmap

Planned improvements include:

* Enhanced Noticeboard & Calendar
* Library Management Enhancements
* Student Attendance Module
* SMS Notifications
* Email Notifications
* Mobile Application Integration
* REST API Support
* Multi-School Support

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Maintainer

**Gerald Masaba**

* GitHub: https://github.com/masabagerald
* LinkedIn: https://www.linkedin.com/in/masabagerald/
