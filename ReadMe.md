# BasicEMS

<p align="center">
	<img src="public/og.png" alt="BasicEMS banner" width="100%" />
</p>

<p align="center">
	Lightweight employee management for very small businesses and tiny teams.
</p>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
    - [Windows](#windows)
    - [Linux](#linux)
    - [macos](#macos)
- [Running the App](#running-the-app)
- [Demo Data](#demo-data)
- [Authentication and Access](#authentication-and-access)
- [Status Values](#status-values)
- [Testing](#testing)

## Overview

BasicEMS is a lightweight employee management system built for very small businesses (barber shops, kiosks, small retail) and personal teams. It focuses on practical day-to-day tracking without enterprise complexity.

> [!WARNING]
> This project is intentionally scoped for small-scale usage. It is not designed for multi-department organizations or high-volume operations. This project will receive huge updates overtime and you might lose your data, ONLY USE WHEN THERE WILL BE AN ACTUAL HOSTING AT BASIC-EMS.LARAVEL.CLOUD

## Features

- Employee directory with contact info, department, job title, and work times.
- Attendance tracking with daily employee records.
- Task management with status, due date, and employee assignment.
- Notes system with title and optional description.
- Due payments tracking with upcoming pay dates and urgency cues.
- Search across employees, attendance, tasks, and notes.
- Authentication with Fortify, including optional two-factor settings.
- Per-user ownership and policies to keep data scoped to the signed-in user.
- Improved code quality and stronger error handling for better reliability.

## Tech Stack

- Laravel 12
- Livewire 4 + Flux UI
- Tailwind CSS 4
- Laravel Fortify

## Requirements

- PHP 8.2+ ( PHP 8.5 Recommended )
- Composer ( Latest version recommeneded )
- Node.js and npm ( Node.js 25 Recommended)
- A Laravel-supported database (SQLite, MySQL, etc. Recommened SQLite for development )

## Quick Start

### Windows

```cmd
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Linux

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### macOS

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Running the App

If you use Laravel Herd on macOS or Windows, the app is served automatically.

Optional manual development command:

```bash
composer run dev
```

This runs the Laravel server, queue listener, and Vite dev server together.

## Demo Data

```bash
php artisan db:seed
```

Seeding creates a test user (test@example.com) and sample employees, attendance records, tasks, notes, and due payments.

## Authentication and Access

- Dashboard needs you to be logged in. Will not let you access.
- Settings include profile, password, appearance, and two-factor options.

## Status Values

- Task statuses: pending, in_progress, completed.
- Due payment statuses: pending, paid.

## Testing

```bash
php artisan test --compact
```
