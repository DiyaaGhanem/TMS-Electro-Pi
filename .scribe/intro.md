# Introduction

Welcome to the **Task Management System (TMS) API** — built for **Electro Pi**.

This API allows you to manage your projects and tasks efficiently with a clean, RESTful interface.

## Features
- 🔐 Secure authentication using Laravel Sanctum
- 📁 Full project management (Create, Read, Update, Delete)
- ✅ Task management with priority and status tracking
- 📊 Dashboard with real-time statistics
- 🔔 Overdue task notifications

## Getting Started
1. Register a new account or login with existing credentials
2. Use the returned token in the `Authorization` header as `Bearer {token}`
3. Start managing your projects and tasks!

> All authenticated endpoints are marked with `requires authentication`.