<div align="center">

# Faydev Dashboard

Admin panel for managing content on [faydev.my.id](https://faydev.my.id).

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479a1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Apache](https://img.shields.io/badge/Apache-.htaccess-important?style=flat-square)]()

[Overview](#overview) · [Features](#features) · [Tech Stack](#tech-stack) · [Project Structure](#project-structure) · [Getting Started](#getting-started) · [API Reference](#api-reference) · [Security](#security) · [Known Notes](#known-notes) · [Roadmap](#roadmap)

</div>

## Overview

Faydev Dashboard is a standalone CMS admin panel running at `panel.faydev.my.id`.
It writes content to the same MySQL database used by the public landing page, including:

- Portfolio projects (`projects`)
- Social media links (`social_links`)

The project is intentionally simple: vanilla PHP + vanilla JavaScript, no Composer, no npm, no build pipeline.

## Features

### Implemented

- Admin login/logout with session auth (`admins` table)
- CSRF protection on all POST mutations
- Session timeout (2 hours inactivity)
- Dashboard KPIs (project count and social link count)
- Projects CRUD:
    - create/edit/delete
    - thumbnail upload (JPG/PNG/WEBP, max 5MB)
    - optional server-side resize via GD
- Social links CRUD:
    - create/edit/delete
    - ordering controls (up/down) persisted via API
- Responsive layout:
    - collapsible sidebar on desktop
    - slide-in sidebar overlay on mobile
- Theme switch (dark/light) persisted in local storage

### Planned

- Content section management (hero/about/skills/services/contact/footer)
- SEO management and additional dashboard modules
- More advanced admin tooling (bulk ops, activity history, etc.)

## Tech Stack

- Backend: PHP 7.4+ (PDO)
- Database: MySQL 5.7+
- Frontend: Vanilla JavaScript + CSS
- Web server: Apache (`.htaccess` rules)
- Assets: Font Awesome + Google Fonts (CDN)

## Project Structure

```text
panel.faydev.my.id/
|-- index.php
|-- login.php
|-- projects.php
|-- project-form.php
|-- social.php
|-- social-form.php
|-- database-dashboard.sql
|-- .htaccess
|
|-- api/
|   |-- auth.php
|   |-- dashboard.php
|   |-- projects.php
|   `-- social.php
|
|-- includes/
|   |-- auth.php
|   |-- csrf.php
|   |-- db.php
|   |-- upload.php
|   |-- header.php
|   `-- footer.php
|
|-- src/
|   |-- css/dashboard.css
|   `-- js/
|       |-- dashboard.js
|       |-- index.js
|       |-- login.js
|       |-- projects.js
|       |-- project-form.js
|       |-- social.js
|       `-- social-form.js
|
`-- assets/images/uploads/
```

## Getting Started

### Prerequisites

- PHP 7.4+ with extensions:
    - `pdo_mysql`
    - `fileinfo`
    - `gd` (recommended for resize, optional fallback supported)
- MySQL 5.7+
- Apache with `mod_rewrite` and `mod_headers`

### 1. Import migration

Run migration into your existing database:

```bash
mysql -u root -p fayd7716_project < database-dashboard.sql
```

This migration will:

- create `admins`
- create `site_settings`
- add `display_order` to `projects` and `social_links` (idempotent)
- seed default admin account

### 2. Configure database connection

Edit `includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fayd7716_project');
```

> [!WARNING]
> Do not store production secrets directly in versioned source files.

### 3. Run with Apache/XAMPP

Put the project in your web root, then open:

```text
http://localhost/panel.faydev.my.id/login.php
```

### 4. Login (first run)

Default seeded credentials:

| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin123` |

> [!WARNING]
> Change the default password immediately after first login.

## API Reference

All endpoints require an authenticated admin session, except `POST /api/auth.php?action=login`.

All mutation endpoints require CSRF token via one of:

- `X-CSRF-Token` request header
- `csrf_token` form/json field

### Standard response format

```json
{ "success": true, "data": { "...": "..." } }
{ "success": false, "message": "Error message" }
```

### Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/auth.php?action=login` | Login admin |
| POST | `/api/auth.php?action=logout` | Logout admin |
| GET | `/api/dashboard.php` | Dashboard metrics |
| GET | `/api/projects.php` | List all projects |
| GET | `/api/projects.php?id={id}` | Get single project |
| POST | `/api/projects.php?action=create` | Create project |
| POST | `/api/projects.php?action=update` | Update project |
| POST | `/api/projects.php?action=delete` | Delete one/many projects |
| GET | `/api/social.php` | List all social links |
| GET | `/api/social.php?id={id}` | Get single social link |
| POST | `/api/social.php?action=create` | Create social link |
| POST | `/api/social.php?action=update` | Update social link |
| POST | `/api/social.php?action=delete` | Delete one/many social links |
| POST | `/api/social.php?action=reorder` | Persist social links display order |

## Security

- Password hashing with `password_hash()` / `password_verify()`
- Session fixation mitigation on login (`session_regenerate_id(true)`)
- Session cookie hardening (`httponly`, `samesite=Lax`, conditional `secure`)
- CSRF checks for POST requests
- Input validation + prepared statements in API handlers
- HTML escaping in rendered templates
- `.htaccess` protections:
    - deny direct access to `includes/`
    - deny `.sql`, `.md`, `.gitignore`
    - disable directory listing
    - set baseline security headers

## Known Notes

> [!NOTE]
> Upload storage is currently under `assets/images/uploads/`.
>
> If your public site expects another path convention, align both apps before deployment.

> [!NOTE]
> There is no automated test suite in this repository yet.

## Roadmap

| Phase | Scope | Status |
|---|---|---|
| 1 | Auth, dashboard KPI, projects CRUD, social CRUD | In progress |
| 2 | Content section CMS + SEO fields | Planned |
| 3 | Advanced admin tools and quality-of-life improvements | Planned |
