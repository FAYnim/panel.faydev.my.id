---
goal: Phase 1 MVP — Auth + Projects CRUD + Social Links CRUD + Dashboard Home
version: 1.0
date_created: 2026-03-17
last_updated: 2026-03-17
owner: Faris AY
status: 'In progress'
tags: [feature, dashboard, phase-1, mvp]
---

# Introduction

![Status: In progress](https://img.shields.io/badge/status-In%20progress-yellow)

Phase 1 delivers the minimum viable admin dashboard at `panel.faydev.my.id`. Scope: authentication, dashboard home overview, Projects CRUD with thumbnail upload, and Social Links CRUD with reorder. The dashboard is a **standalone PHP + vanilla JS project** sharing the same MySQL database (`fayd7716_project`) as the landing page at `faydev.my.id`.

## 1. Requirements & Constraints

- **REQ-001**: Single admin user login with bcrypt password, PHP sessions, 2h inactivity timeout
- **REQ-002**: CSRF token on every POST request
- **REQ-003**: Session regeneration on login, HTTP-only + SameSite=Lax cookies
- **REQ-004**: Dashboard home shows project count, social link count, quick-action buttons
- **REQ-005**: Projects CRUD — create, read, update, delete with thumbnail upload (JPEG/PNG/WebP, max 5MB, auto-resize to 1200x750)
- **REQ-006**: Social Links CRUD — create, read, update, delete with display_order reordering
- **REQ-007**: Dashboard API returns `{ "success": true, "data": {...} }` / `{ "success": false, "message": "..." }` matching landing page convention
- **REQ-008**: All dashboard API endpoints require active admin session (401 if unauthorized)
- **REQ-009**: Responsive layout (sidebar + main content), dark-first with light mode toggle
- **SEC-001**: Prepared statements (PDO parameterized queries) for all DB operations
- **SEC-002**: File upload validation — MIME whitelist, size limit, randomized filenames
- **SEC-003**: XSS prevention via `htmlspecialchars()` on output
- **SEC-004**: No `Access-Control-Allow-Origin: *` on dashboard APIs (session-only)
- **CON-001**: No npm, no Composer, no build tools — vanilla PHP + JS only
- **CON-002**: Same database `fayd7716_project`, same `getDB()` connection pattern
- **CON-003**: PHP >= 7.4, MySQL >= 5.7, Apache with mod_rewrite
- **CON-004**: Images uploaded to landing page's `assets/images/uploads/` (shared hosting — same document root parent)
- **GUD-001**: Reuse landing page design tokens (green palette, Syne + DM Sans fonts, CSS custom properties)
- **PAT-001**: Follow existing `api/projects.php` and `api/social.php` response format
- **PAT-002**: Follow existing `includes/db.php` PDO singleton pattern

## 2. Implementation Steps

### Phase 1.1 — Repository Cleanup & Structure

- GOAL-001: Remove landing page files from dashboard repo and establish the PRD-specified directory structure

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-001 | Remove landing page files (`index.php` landing HTML, `assets/css/style.css` landing styles, `assets/js/main.js` landing JS, `api/projects.php` public API, `api/social.php` public API, `database.sql` landing schema, `assets/images/`) | ✅ | 2026-03-16 |
| TASK-002 | Create directory structure: `pages/`, `api/`, `includes/`, `assets/css/`, `assets/js/`, `assets/images/` | ✅ | 2026-03-16 |
| TASK-003 | Keep `includes/db.php` (unchanged), `PRD.md`, `PRD-DASHBOARD.md`, `README.md` | ✅ | 2026-03-16 |

### Phase 1.2 — Database Migration

- GOAL-002: Create all new tables and modify existing tables per PRD schema

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-004 | Create `database-dashboard.sql` with: `admins` table (id, username, password_hash, created_at, updated_at) | ✅ | 2026-03-16 |
| TASK-005 | Add `site_settings` table (id, section, key, value, created_at, updated_at) with composite unique on (section, key) | ✅ | 2026-03-16 |
| TASK-006 | Add ALTER TABLE `projects` ADD COLUMN `display_order` INT NOT NULL DEFAULT 0 | ✅ | 2026-03-16 |
| TASK-007 | Add ALTER TABLE `social_links` ADD COLUMN `display_order` INT NOT NULL DEFAULT 0 | ✅ | 2026-03-16 |
| TASK-008 | Add INSERT for default admin user (username: `admin`, password: bcrypt hash of a temp password) | ✅ | 2026-03-16 |

### Phase 1.3 — Authentication System

- GOAL-003: Implement login/logout with session management and CSRF protection

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-009 | Create `includes/auth.php` — `requireAuth()`, `isLoggedIn()`, `getCurrentAdmin()`, session timeout check (2h), session regeneration | ✅ | 2026-03-16 |
| TASK-010 | Create `includes/csrf.php` — `generateCsrfToken()`, `validateCsrfToken()`, per-session token | ✅ | 2026-03-16 |
| TASK-011 | Create `api/auth.php` — POST login (validate credentials, start session), POST logout (destroy session) | ✅ | 2026-03-16 |
| TASK-012 | Create `login.php` — login form page with CSRF token, error display, redirect to dashboard on success | ✅ | 2026-03-16 |

### Phase 1.4 — Dashboard UI Shell

- GOAL-004: Build the shared layout (sidebar, header, theme toggle, responsive) used by all dashboard pages

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-013 | Create `includes/header.php` — HTML head, sidebar nav (logo, nav links for all sections, logout), main content area wrapper open, theme toggle | ✅ | 2026-03-17 |
| TASK-014 | Create `includes/footer.php` — main content area wrapper close, JS includes, toast notification container | ✅ | 2026-03-17 |
| TASK-015 | Create `assets/css/dashboard.css` — full dashboard design system reusing landing page tokens (green palette, Syne + DM Sans, dark/light themes), sidebar (240px, collapsible to 60px), responsive breakpoints, form styles, table styles, card styles, toast styles, modal styles | ✅ | 2026-03-16 |
| TASK-016 | Create `assets/js/dashboard.js` — theme toggle (localStorage persistence), sidebar toggle, toast notifications, unsaved changes warning, CSRF token injection for fetch calls, confirm dialogs | ✅ | 2026-03-16 |

### Phase 1.5 — Dashboard Home

- GOAL-005: Build the dashboard home page showing content overview and quick actions

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-017 | Create `index.php` (dashboard) — redirect to login if not authenticated, otherwise show dashboard home | ✅ | 2026-03-17 |
| TASK-018 | Create `api/dashboard.php` — GET returns stats (project count, social link count, last updated timestamps) | ✅ | 2026-03-17 |
| TASK-019 | Dashboard home UI: KPI cards (total projects, total social links), quick-action buttons (Add Project, Manage Social Links) | ✅ | 2026-03-17 |

### Phase 1.6 — Projects CRUD

- GOAL-006: Full CRUD for projects with thumbnail upload, pagination, and bulk delete

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-020 | Create `includes/upload.php` — `handleImageUpload($file, $maxWidth, $maxHeight, $maxSize)`, GD resize, unique filename, MIME validation | ✅ | 2026-03-17 |
| TASK-021 | Create `api/projects.php` (dashboard) — GET list all (paginated), POST create, POST update, POST delete (single + bulk) | ✅ | 2026-03-17 |
| TASK-022 | Create `pages/projects.php` — project list table (thumbnail preview, title, date, demo link, actions), Add button, pagination | ✅ | 2026-03-17 |
| TASK-023 | Create `pages/project-form.php` — create/edit form (title, thumbnail upload with drag-drop + preview, demo link URL, project date), save button | ✅ | 2026-03-17 |

### Phase 1.7 — Social Links CRUD

- GOAL-007: Full CRUD for social links with icon preview and display order reordering

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-024 | Create `api/social.php` (dashboard) — GET list all, POST create, POST update, POST delete, POST reorder | ✅ | 2026-03-17 |
| TASK-025 | Create `pages/social.php` — social links table (icon preview, name, URL, display order, actions), Add button, reorder UI | ✅ | 2026-03-17 |
| TASK-026 | Create `pages/social-form.php` — create/edit form (platform name, FA icon class with live preview, URL), save button, duplicate name validation | ✅ | 2026-03-17 |

### Phase 1.8 — Testing & Polish

- GOAL-008: Verify all flows work end-to-end and fix edge cases

| Task | Description | Completed | Date |
|------|-------------|-----------|------|
| TASK-027 | Test login flow: correct credentials, wrong credentials, session expiry, CSRF validation | | |
| TASK-028 | Test projects CRUD: create with thumbnail, edit, delete, bulk delete, pagination | | |
| TASK-029 | Test social links CRUD: create, edit, delete, reorder, duplicate name rejection | | |
| TASK-030 | Test responsive layout: desktop sidebar, mobile hamburger overlay | | |
| TASK-031 | Test dark/light theme toggle persistence across pages | | |

## 3. Alternatives

- **ALT-001**: Use a PHP framework (Laravel/Slim) — rejected per PRD constraint of no external dependencies
- **ALT-002**: SPA with React/Vue — rejected per PRD constraint of vanilla JS, no build tools
- **ALT-003**: JSON file storage instead of MySQL — rejected, PRD requires shared MySQL database

## 4. Dependencies

- **DEP-001**: PHP >= 7.4 with `pdo_mysql` and `gd` extensions (standard on XAMPP)
- **DEP-002**: MySQL >= 5.7 (existing `fayd7716_project` database)
- **DEP-003**: Font Awesome 6.5.x (CDN) — for icons in sidebar and forms
- **DEP-004**: Google Fonts (Syne + DM Sans) — CDN, matching landing page
- **DEP-005**: Apache with mod_rewrite (for .htaccess auth protection)

## 5. Files

- **FILE-001**: `database-dashboard.sql` — migration script for new tables + ALTER existing
- **FILE-002**: `includes/db.php` — existing, unchanged
- **FILE-003**: `includes/auth.php` — session management helpers
- **FILE-004**: `includes/csrf.php` — CSRF token generation/validation
- **FILE-005**: `includes/upload.php` — image upload/resize helpers
- **FILE-006**: `includes/header.php` — shared dashboard header + sidebar
- **FILE-007**: `includes/footer.php` — shared dashboard footer + JS
- **FILE-008**: `login.php` — login page
- **FILE-009**: `index.php` — dashboard home (redirects to login if unauthenticated)
- **FILE-010**: `api/auth.php` — login/logout API
- **FILE-011**: `api/dashboard.php` — dashboard stats API
- **FILE-012**: `api/projects.php` — projects CRUD API (dashboard, session-protected)
- **FILE-013**: `api/social.php` — social links CRUD API (dashboard, session-protected)
- **FILE-014**: `api/upload.php` — image upload API
- **FILE-015**: `pages/projects.php` — projects list page
- **FILE-016**: `pages/project-form.php` — project create/edit form
- **FILE-017**: `pages/social.php` — social links list page
- **FILE-018**: `pages/social-form.php` — social link create/edit form
- **FILE-019**: `assets/css/dashboard.css` — dashboard styles
- **FILE-020**: `assets/js/dashboard.js` — dashboard client-side logic
- **FILE-021**: `.htaccess` — rewrite rules and security headers

## 6. Testing

- **TEST-001**: Login with correct credentials → session created, redirected to dashboard
- **TEST-002**: Login with wrong credentials → error message, no session
- **TEST-003**: Access dashboard without session → redirected to login
- **TEST-004**: Session expires after 2h inactivity → redirected to login
- **TEST-005**: CSRF token missing/invalid on POST → request rejected
- **TEST-006**: Create project with valid thumbnail → saved, thumbnail resized and stored
- **TEST-007**: Create project with oversized file → error returned, file rejected
- **TEST-008**: Edit project → fields updated, old thumbnail deleted if replaced
- **TEST-009**: Delete project → removed from DB
- **TEST-010**: Create social link with duplicate name → error returned
- **TEST-011**: Reorder social links → display_order updated in DB
- **TEST-012**: Dashboard home shows correct counts from DB
- **TEST-013**: Theme toggle persists across page navigation

## 7. Risks & Assumptions

- **RISK-001**: Cross-subdomain file writes — dashboard may not have write access to landing page's upload directory. Mitigation: uploads stored in dashboard's own `assets/images/uploads/` and landing page references them via full URL.
- **RISK-002**: PHP GD extension not available — fallback: store original image without resize, warn admin.
- **ASSUMPTION-001**: Database `fayd7716_project` already exists with `projects` and `social_links` tables populated.
- **ASSUMPTION-002**: Apache mod_rewrite is available for .htaccess rules.
- **ASSUMPTION-003**: PHP session handler works with default file-based storage.

## 8. Related Specifications / Further Reading

- [PRD-DASHBOARD.md](../../PRD-DASHBOARD.md) — Full product requirements document
- [PRD.md](../../PRD.md) — Landing page product requirements
