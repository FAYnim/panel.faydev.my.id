<div align="center">

# Faydev Dashboard

**CMS admin panel for [faydev.my.id](https://faydev.my.id)**

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479a1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![No Dependencies](https://img.shields.io/badge/Dependencies-None-brightgreen?style=flat-square)]()

[Overview](#overview) · [Features](#features) · [Project Structure](#project-structure) · [Getting Started](#getting-started) · [API Reference](#api-reference) · [Security](#security) · [Roadmap](#roadmap)

</div>

---

## Overview

A standalone admin dashboard at `panel.faydev.my.id` for managing the content displayed on the [faydev.my.id](https://faydev.my.id) landing page. Both apps share the same MySQL database — the dashboard writes, the landing page reads.

Built with vanilla PHP + JavaScript. No Composer, no npm, no build tools.

## Features

**Phase 1 (current):**

- Session-based authentication with bcrypt passwords and CSRF protection
- Projects CRUD — add, edit, delete portfolio projects with image upload and drag-drop reordering
- Social Links CRUD — manage social media links with Font Awesome icon picker and display ordering
- Dashboard home with KPI cards (project count, social link count, last-updated timestamps)
- Dark / light theme toggle
- Responsive sidebar layout (collapsible on desktop, overlay on mobile)

## Project Structure

```
panel.faydev.my.id/
├── index.php                    # Dashboard home
├── login.php                    # Authentication page
├── database-dashboard.sql       # Migration script (admins, site_settings, display_order)
├── .htaccess                    # Security headers, directory protection
│
├── api/
│   ├── auth.php                 # POST login / logout
│   ├── dashboard.php            # GET stats (counts, timestamps)
│   ├── projects.php             # CRUD for projects
│   └── social.php               # CRUD for social links
│
├── includes/
│   ├── db.php                   # PDO connection singleton
│   ├── auth.php                 # Session management (2h timeout)
│   ├── csrf.php                 # CSRF token generation / validation
│   ├── upload.php               # Image upload + GD resize
│   ├── header.php               # Shared layout header + sidebar
│   └── footer.php               # Shared layout footer + scripts
│
├── pages/
│   ├── projects.php             # Project list with data table
│   ├── project-form.php         # Add / edit project form
│   ├── social.php               # Social links list
│   └── social-form.php          # Add / edit social link form
│
└── assets/
    ├── css/dashboard.css        # Design system (tokens, themes, components)
    ├── js/dashboard.js          # Client-side logic (API wrapper, toasts, modals)
    └── images/uploads/          # Uploaded project thumbnails
```

## Getting Started

### Prerequisites

- PHP 7.4+ with `pdo_mysql` and `gd` extensions
- MySQL 5.7+
- Apache with `mod_rewrite` (or XAMPP)

### 1. Database setup

The dashboard shares the `fayd7716_project` database with the landing page. Run the migration to add dashboard-specific tables:

```bash
mysql -u root -p fayd7716_project < database-dashboard.sql
```

Or import `database-dashboard.sql` via phpMyAdmin.

This creates the `admins` and `site_settings` tables, adds `display_order` to `projects` and `social_links`, and seeds a default admin user.

### 2. Configure database connection

Edit `includes/db.php` with your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'fayd7716_project');
```

> [!WARNING]
> Do not commit production credentials. Use environment variables or a server-level config on deployed environments.

### 3. Start the server

Place the project in your Apache webroot and visit:

```
http://localhost/panel.faydev.my.id/login.php
```

### 4. Log in

Default credentials:

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> [!WARNING]
> Change the default password immediately after first login.

## API Reference

All endpoints require an active admin session. Unauthorized requests return `401`.

Mutation endpoints (POST) require a CSRF token via the `X-CSRF-Token` header or `csrf_token` POST field.

### Response format

```json
{ "success": true, "data": { ... } }
{ "success": false, "message": "Error description" }
```

### Endpoints

| Method | Endpoint | Action | Description |
|--------|----------|--------|-------------|
| POST | `/api/auth.php` | `login` | Authenticate with username + password |
| POST | `/api/auth.php` | `logout` | Destroy session |
| GET | `/api/dashboard.php` | — | Project/social counts and last-updated timestamps |
| GET | `/api/projects.php` | — | List all projects |
| POST | `/api/projects.php?action=create` | `create` | Create project (multipart form) |
| POST | `/api/projects.php?action=update` | `update` | Update project by ID |
| POST | `/api/projects.php?action=delete` | `delete` | Delete project(s) by ID |
| GET | `/api/social.php` | — | List all social links |
| POST | `/api/social.php?action=create` | `create` | Create social link |
| POST | `/api/social.php?action=update` | `update` | Update social link by ID |
| POST | `/api/social.php?action=delete` | `delete` | Delete social link(s) |
| POST | `/api/social.php?action=reorder` | `reorder` | Batch update display order |

## Security

- Passwords hashed with `password_hash()` (bcrypt)
- Session timeout after 2 hours of inactivity
- CSRF tokens on all state-changing requests
- `.htaccess` blocks direct access to `includes/`, `docs/`, `.sql`, and `.md` files
- Security headers: `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`
- API endpoints are session-only (no `Access-Control-Allow-Origin: *`)
- All user input escaped with `htmlspecialchars()` in templates
- Prepared statements for all database queries

## Roadmap

| Phase | Scope | Status |
|-------|-------|--------|
| 1 — MVP | Auth, Projects CRUD, Social Links CRUD, Dashboard Home | In progress |
| 2 — Content Sections | Hero, About, Skills, Services, Contact, Footer, SEO management | Planned |
| 3 — Advanced | Bulk operations, activity log, image optimization, analytics widget | Planned |

> [!NOTE]
> See [`PRD-DASHBOARD.md`](PRD-DASHBOARD.md) for the full product requirements document.
