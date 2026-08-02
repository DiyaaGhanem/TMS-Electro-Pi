# 🗂️ TMS — Task Management System API

> Built for **Electro Pi** Technical Assessment | Laravel 11 REST API

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [Architecture & Design Patterns](#-architecture--design-patterns)
- [Features](#-features)
- [Project Structure](#-project-structure)
- [Setup & Installation](#-setup--installation)
- [Running the Tests](#-running-the-tests)
- [API Documentation](#-api-documentation)
- [Default Test User](#-default-test-user)
- [Overdue Notification System](#-overdue-notification-system)
- [Development Timeline](#-development-timeline)

---

## 📌 Project Overview

TMS is a RESTful API for a **Task Management System** built with Laravel 11. It allows authenticated users to manage their own projects and tasks, with support for filtering, searching, pagination, dashboard statistics, and automated overdue task notifications.

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Authentication | Laravel Sanctum |
| Database | MySQL |
| API Documentation | Scribe (OpenAPI/Swagger compatible) |
| Queue System | Laravel Jobs & Queues |
| Testing | PHPUnit (Feature Tests) |
| Version Control | Git / GitHub |

---

## 🏗 Architecture & Design Patterns

The project follows a clean, layered architecture to ensure separation of concerns and maintainability:

### Service Layer Pattern
All business logic lives in dedicated **Service classes** , Controllers are kept thin and only responsible for handling HTTP requests and returning responses.

```
Request → Controller → Service → Model → Database
```

### Applied Patterns & Principles

| Pattern / Principle | Where Applied |
|---|---|
| **Service Layer** | `AuthService`, `ProjectService`, `TaskService`, `DashboardService` |
| **Repository-like abstraction** | Services abstract all Eloquent queries away from Controllers |
| **Form Request Validation** | `StoreProjectRequest`, `UpdateProjectRequest`, `StoreTaskRequest`, `UpdateTaskRequest` |
| **API Resources** | `UserResource`, `ProjectResource`, `TaskResource` |
| **Traits** | `Responses` trait for consistent JSON response formatting |
| **Enums** | `ProjectStatus`, `TaskStatus`, `TaskPriority` for type-safe values |
| **Soft Deletes** | Applied on `Project` and `Task` models |
| **Queue Jobs** | `SendOverdueNotificationJob` for async processing |
| **Artisan Commands** | `tasks:send-overdue-notifications` custom command |
| **Middleware** | `ForceJsonResponse` to ensure all API responses are JSON |

---

## ✅ Features

### Authentication
- Register, Login, Logout via Laravel Sanctum
- Token-based API authentication

### Projects Module
- Full CRUD (Create, Read, Update, Delete)
- Ownership-based access control (users can only manage their own projects)
- Soft deletes
- Paginated listing with task count
- Single project view includes all related tasks

### Tasks Module
- Full CRUD per project
- Filter by **status** (`todo`, `in_progress`, `done`)
- Filter by **priority** (`low`, `medium`, `high`)
- Search by **title**
- Paginated results
- Soft deletes
- Overdue tracking via `overdue_notified` flag

### Dashboard
- Single endpoint returning:
  - Total Projects
  - Active Projects
  - Total Tasks
  - Completed Tasks
  - Pending Tasks
  - Overdue Tasks

### Overdue Notification System
- Custom Artisan command scans all overdue tasks
- Dispatches queue jobs in chunks of 10
- Each job logs a simulated email notification
- Updates `overdue_notified` flag after processing
- Reports total notified count

---

## 📁 Project Structure

```
app/
├── Console/Commands/
│   └── SendOverdueTaskNotifications.php
├── Enums/
│   ├── ProjectStatus.php
│   ├── TaskStatus.php
│   └── TaskPriority.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProjectController.php
│   │   ├── TaskController.php
│   │   └── DashboardController.php
│   ├── Middleware/
│   │   └── ForceJsonResponse.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── RegisterRequest.php
│   │   │   └── LoginRequest.php
│   │   ├── Project/
│   │   │   ├── StoreProjectRequest.php
│   │   │   └── UpdateProjectRequest.php
│   │   └── Task/
│   │       ├── StoreTaskRequest.php
│   │       └── UpdateTaskRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── ProjectResource.php
│       └── TaskResource.php
├── Jobs/
│   └── SendOverdueNotificationJob.php
├── Models/
│   ├── User.php
│   ├── Project.php
│   └── Task.php
├── Notifications/
│   └── TaskOverdueNotification.php
├── Services/
│   ├── AuthService.php
│   ├── ProjectService.php
│   ├── TaskService.php
│   └── DashboardService.php
└── Traits/
    └── Responses.php
```

---

## 🚀 Setup & Installation

### Requirements

- PHP >= 8.2
- Composer
- MySQL
- Laravel 11

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/DiyaaGhanem/TMS-Electro-Pi.git
cd TMS
```

**2. Install dependencies**
```bash
composer install
```

**3. Copy environment file**
```bash
cp .env.example .env
```

**4. Configure your `.env` file**
```env
APP_NAME=TMS
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tms
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

**5. Generate application key**
```bash
php artisan key:generate
```

**6. Run migrations**
```bash
php artisan migrate
```

**7. Seed the database**
```bash
php artisan db:seed
```

**8. Start the development server**
```bash
php artisan serve
```

---

## 🧪 Running the Tests

The project includes **Feature Tests** covering Auth, Projects, and Tasks modules.

**Run all tests:**
```bash
php artisan test
```

**Run specific test suites:**
```bash
# Auth tests only
php artisan test --filter=AuthTest

# Project tests only
php artisan test --filter=ProjectTest

# Task tests only
php artisan test --filter=TaskTest
```

**Expected output:**
```
Tests:    XX passed
Duration: X.XXs
```

> ⚠️ Tests use `RefreshDatabase` — a separate test database is recommended. Configure `DB_DATABASE` in `phpunit.xml` or use an in-memory SQLite database.

---

## 📖 API Documentation

The API is documented using **Scribe** (OpenAPI/Swagger compatible).

**1. Generate the docs:**
```bash
php artisan scribe:generate
```

**2. Start the server:**
```bash
php artisan serve
```

**3. Open in your browser:**
```
http://127.0.0.1:8000/docs
```

**4. Download Postman Collection:**

Visit `http://127.0.0.1:8000/docs.postman` or click **"View Postman collection"** in the docs sidebar.

---

## 👤 Default Test User

After running the seeder, you can login with:

```json
{
    "email": "test@example.com",
    "password": "password"
}
```

> The seeder also creates 4 additional random users, each with 3 projects and 5 tasks per project.

---

## 🔔 Overdue Notification System

The system uses Laravel Queues to send overdue task notifications asynchronously.

**Step 1 — Start the queue worker (in a separate terminal):**
```bash
php artisan queue:work
```

**Step 2 — Run the notification command:**
```bash
php artisan tasks:send-overdue-notifications
```

**What happens:**
1. Scans all tasks where `due_date` has passed and `overdue_notified = false`
2. Dispatches jobs in chunks of 10 tasks
3. Each job logs a simulated email notification to `storage/logs/laravel.log`
4. Updates `overdue_notified` flag to `true` after processing
5. Reports total number of notified tasks

**Check the logs:**
```bash
tail -f storage/logs/laravel.log
```

---

## ⏱ Development Timeline

| Task | Time Spent |
|---|---|
| Project setup (Laravel 11, Sanctum, Scribe, Git) | ~20 min |
| Migrations, Models, Relations, Enums | ~30 min |
| Seeders & Factories | ~20 min |
| Responses Trait | ~10 min |
| Authentication Module (Register, Login, Logout) | ~30 min |
| Scribe Documentation Setup & Annotations | ~40 min |
| Projects Module (CRUD + Authorization) | ~45 min |
| Tasks Module (CRUD + Filters + Search) | ~40 min |
| Dashboard Endpoint | ~20 min |
| Overdue Notification System (Job + Command) | ~35 min |
| Feature Tests (Auth + Projects + Tasks) | ~45 min |
| README & Final Documentation | ~30 min |
| **Total** | **~6 hours** |

---

## 🔗 Links

- **GitHub Repository:** [github.com/YOUR_USERNAME/TMS](https://github.com/YOUR_USERNAME/TMS)
- **API Documentation:** `http://127.0.0.1:8000/docs`
- **Postman Collection:** Available in `/storage/app/scribe/`

---