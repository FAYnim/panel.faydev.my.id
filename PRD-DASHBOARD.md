# Product Requirements Document (PRD)

## Faydev Landing Page Dashboard — Full CMS Admin Panel

**Version**: 1.0
**Date**: 2026-03-16
**Product**: panel.faydev.my.id
**Status**: Draft

---

# 1. Executive Summary

## 1.1 Problem Statement

All landing page content at faydev.my.id is currently split between hardcoded HTML (hero, about, skills, services, contact, footer) and two MySQL tables (projects, social links). Updating any hardcoded section requires SSH/FTP access and editing raw PHP files — a fragile process that risks breaking the production site and is inaccessible to non-developers.

## 1.2 Proposed Solution

A standalone admin dashboard at **panel.faydev.my.id** that provides a full CMS for managing every section of the landing page. The dashboard converts all hardcoded content into database-driven records exposed via PHP JSON APIs, consumed by the existing vanilla JS frontend. The admin authenticates with a simple PHP session login (single admin user).

## 1.3 Success Criteria

| KPI | Target | Measurement |
|---|---|---|
| Content update time | < 2 minutes for any section edit | Manual test: edit hero text, save, verify on landing page |
| Zero-downtime updates | 0 broken page loads during content changes | Landing page falls back to cached/default content if API fails |
| Admin login security | Bcrypt password hash, session expiry < 2h, CSRF token on all forms | Code review + security checklist |
| Landing page load time | < 2 seconds (unchanged from current) | Lighthouse performance score >= 90 |
| API response time | < 100ms per endpoint | Measured via browser devtools on shared hosting |

---

# 2. User Experience & Functionality

## 2.1 User Personas

### Admin (Primary — Faris AY)

- **Role**: Solo developer and site owner
- **Goal**: Update landing page content (text, images, links, projects, skills) without editing code
- **Technical level**: High (developer), but wants a faster workflow than FTP + SQL
- **Access pattern**: Occasional edits (few times per month), bulk updates when adding new projects

### Future Collaborator (Secondary — stretch goal)

- **Role**: Virtual assistant or partner who may help manage content
- **Goal**: Add projects or update social links without technical knowledge
- **Technical level**: Low

## 2.2 User Stories & Acceptance Criteria

### US-01: Admin Login

> As the admin, I want to log in with username and password so that only I can access the dashboard.

**Acceptance Criteria:**
- Login form with username and password fields
- Password stored as bcrypt hash in the `admins` table
- Session created on successful login, persists across pages
- Session expires after 2 hours of inactivity
- Failed login shows error message without revealing which field is wrong ("Invalid credentials")
- CSRF token on the login form
- Redirect to dashboard home after successful login
- Redirect to login page if session is expired/missing on any dashboard route

### US-02: Dashboard Home

> As the admin, I want to see an overview of my site's content at a glance so I can quickly identify what needs updating.

**Acceptance Criteria:**
- Shows total project count, total social links count
- Shows last updated timestamp per section (hero, about, skills, services, contact, SEO)
- Quick-action buttons: "Add Project", "Edit Hero", "Edit Contact"
- Responsive layout (usable on mobile/tablet)

### US-03: Hero Section Management

> As the admin, I want to edit the hero section content so I can update my name, tagline, description, CTAs, profile photo, and stat cards without touching code.

**Acceptance Criteria:**
- Editable fields: name, name accent word, badge text, typed roles (comma-separated list), description (with bold/italic support), CTA button labels + URLs, profile photo upload, stat card values (number + label, 2 cards)
- Profile photo upload: accepts JPEG/PNG/WebP, max 2MB, auto-resized to max 800x800px
- Save button persists changes to the database
- Changes reflected on landing page immediately (no cache to bust — API is fetched on page load)

### US-04: About Section Management

> As the admin, I want to edit my "About Me" content so I can keep my bio and facts current.

**Acceptance Criteria:**
- Editable fields: lead paragraph, body paragraphs (array of text blocks with bold/italic), fact items (icon class + label, dynamic list — add/remove)
- Terminal card content: JSON key-value pairs editable as a structured form (key, value, type: string/number/boolean/array)
- Save persists to database

### US-05: Skills Management

> As the admin, I want to add, edit, reorder, and remove skill groups and individual skills so my skills section stays current.

**Acceptance Criteria:**
- CRUD for skill groups (name, icon class, display order)
- CRUD for individual skills within a group (name, proficiency percentage 0-100, display order)
- Drag-and-drop or up/down arrows to reorder groups and skills
- Changes reflected on landing page via API

### US-06: Services Management

> As the admin, I want to manage my service offerings so I can add, edit, or remove services.

**Acceptance Criteria:**
- CRUD for service cards (title, icon class, description, feature list as line-separated text, CTA URL, is_featured boolean, badge text, display order)
- Maximum 8 services (UI enforced)
- Reorderable via display order
- Changes reflected on landing page via API

### US-07: Projects Management

> As the admin, I want to add, edit, and delete portfolio projects so my gallery stays up to date.

**Acceptance Criteria:**
- CRUD for projects (title, thumbnail upload, demo link URL, project date)
- Thumbnail upload: accepts JPEG/PNG/WebP, max 5MB, auto-resized to max 1200x750px (16:10 ratio)
- Thumbnail preview in the list and edit form
- Sorted by project_date descending by default
- Bulk delete with confirmation modal
- Extends existing `projects` table — no schema-breaking changes

### US-08: Social Links Management

> As the admin, I want to manage my social media links so visitors can find me on the right platforms.

**Acceptance Criteria:**
- CRUD for social links (platform name, Font Awesome icon class, URL)
- Icon preview next to icon class input
- Reorderable via drag-and-drop or display order field
- Duplicate platform name rejected (existing `uq_social_name` constraint enforced in UI)
- Extends existing `social_links` table

### US-09: Contact / CTA Management

> As the admin, I want to edit the contact section so I can update my WhatsApp number, CTA text, and response time note.

**Acceptance Criteria:**
- Editable fields: section title, description (with bold support), WhatsApp number (validated format: country code + digits), CTA button label, response time note text
- WhatsApp number change propagates to ALL wa.me links across the landing page (hero + contact)
- Save persists to database

### US-10: SEO & Meta Management

> As the admin, I want to edit meta tags so I can control how my site appears in search and social shares.

**Acceptance Criteria:**
- Editable fields: page title, meta description, meta keywords, OG title, OG description, OG image upload, Twitter card type
- OG image upload: accepts JPEG/PNG, max 2MB, recommended 1200x630px
- Character counters: title (max 60 chars warning), description (max 160 chars warning)
- Save persists to database

### US-11: Footer Management

> As the admin, I want to edit the footer text so I can update branding and copyright info.

**Acceptance Criteria:**
- Editable fields: brand tagline, copyright text template (with `{year}` placeholder auto-replaced)
- Footer nav links derived from section order (not separately editable)

### US-12: Image Management

> As the admin, I want uploaded images to be stored reliably and served efficiently.

**Acceptance Criteria:**
- Images uploaded via dashboard stored in `assets/images/uploads/` on the landing page server
- Dashboard generates unique filenames (timestamp + hash) to prevent collisions
- Old images deleted when replaced
- Image paths stored as relative URLs in the database

### US-13: Logout

> As the admin, I want to log out so my session is terminated securely.

**Acceptance Criteria:**
- Logout button visible on all dashboard pages
- Destroys PHP session and redirects to login page
- Cannot access dashboard pages after logout without re-authenticating

## 2.3 Non-Goals (Explicitly Out of Scope)

- **Multi-user accounts / RBAC**: Single admin user only. Multi-user is a future consideration.
- **Content versioning / undo**: No revision history. Edits are immediate.
- **Visual page builder / drag-and-drop layout**: Content fields only, not layout editing.
- **Blog / CMS pages beyond the landing page**: This dashboard manages one single-page site.
- **Email notifications or analytics**: No built-in analytics. Use external tools (Google Analytics).
- **API rate limiting**: Single admin user, no public write API.
- **Internationalization (i18n)**: Dashboard UI is in English. Landing page content language is user-controlled.
- **Project detail pages at `/project/{slug}`**: Listed in existing PRD but separate scope.

---

# 3. Technical Specifications

## 3.1 Architecture Overview

```
panel.faydev.my.id/              (Dashboard — this project)
├── index.php                     Login page (or redirect to dashboard if authenticated)
├── dashboard.php                 Dashboard home
├── pages/
│   ├── hero.php                  Hero section editor
│   ├── about.php                 About section editor
│   ├── skills.php                Skills manager
│   ├── services.php              Services manager
│   ├── projects.php              Projects manager
│   ├── social.php                Social links manager
│   ├── contact.php               Contact/CTA editor
│   ├── seo.php                   SEO meta editor
│   └── footer.php                Footer editor
├── api/
│   ├── auth.php                  Login/logout handlers
│   ├── hero.php                  Hero CRUD
│   ├── about.php                 About CRUD
│   ├── skills.php                Skills CRUD
│   ├── services.php              Services CRUD
│   ├── projects.php              Projects CRUD
│   ├── social.php                Social links CRUD
│   ├── contact.php               Contact CRUD
│   ├── seo.php                   SEO CRUD
│   ├── footer.php                Footer CRUD
│   └── upload.php                Image upload handler
├── includes/
│   ├── db.php                    PDO connection (same DB as landing page)
│   ├── auth.php                  Session management helpers
│   ├── csrf.php                  CSRF token generation/validation
│   └── upload.php                Image upload/resize helpers
├── assets/
│   ├── css/dashboard.css         Dashboard styles
│   └── js/dashboard.js           Dashboard client-side logic
└── database-dashboard.sql        Additional tables for dashboard
```

```
faydev.my.id/                    (Landing Page — existing project, modified)
├── index.php                     Now renders from DB content via API calls
├── api/
│   ├── projects.php              Existing — unchanged
│   ├── social.php                Existing — unchanged
│   ├── hero.php                  NEW — serves hero content
│   ├── about.php                 NEW — serves about content
│   ├── skills.php                NEW — serves skills content
│   ├── services.php              NEW — serves services content
│   ├── contact.php               NEW — serves contact content
│   ├── seo.php                   NEW — serves SEO meta content
│   └── footer.php                NEW — serves footer content
└── assets/js/main.js             Modified — fetches all sections from APIs
```

### Data Flow

```
Admin (browser) → panel.faydev.my.id/api/* → MySQL (fayd7716_project) → faydev.my.id/api/* → Landing Page (browser)
```

Both the dashboard and the landing page connect to the **same MySQL database** (`fayd7716_project`). The dashboard writes; the landing page reads.

## 3.2 Database Schema (New Tables)

All new tables use the same database (`fayd7716_project`), charset `utf8mb4`, collation `utf8mb4_unicode_ci`.

### `admins`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Admin ID |
| username | VARCHAR(50) | NOT NULL, UNIQUE | Login username |
| password_hash | VARCHAR(255) | NOT NULL | Bcrypt hash |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update |

### `site_settings`

A key-value store for singleton content sections (hero, about, contact, SEO, footer).

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Setting ID |
| section | VARCHAR(50) | NOT NULL | Section identifier (hero, about, contact, seo, footer) |
| key | VARCHAR(100) | NOT NULL | Setting key |
| value | TEXT | NULL | Setting value (plain text or JSON for complex data) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update |

**Composite unique index**: `(section, key)`

**Example rows:**

| section | key | value |
|---|---|---|
| hero | name | Faris |
| hero | name_accent | AY |
| hero | badge_text | Available for projects |
| hero | typed_roles | ["Software Engineer","Fullstack Developer","Web Developer","Problem Solver"] |
| hero | description | Saya membangun solusi digital yang... |
| hero | cta_primary_label | Lihat Project |
| hero | cta_primary_url | #projects |
| hero | cta_whatsapp_label | Konsultasi WhatsApp |
| hero | profile_image | assets/images/uploads/profile-abc123.jpg |
| hero | stat_1_number | 3+ |
| hero | stat_1_label | Tahun exp. |
| hero | stat_2_number | 30+ |
| hero | stat_2_label | Projects selesai |
| about | lead_paragraph | Halo! Saya **Faris AY**... |
| about | body_paragraphs | ["Berawal dari...","Saya percaya..."] |
| about | facts | [{"icon":"fas fa-map-marker-alt","label":"Indonesia"},{"icon":"fas fa-briefcase","label":"Freelance & Remote"},{"icon":"fas fa-clock","label":"Fast Turnaround"}] |
| about | terminal_data | [{"key":"name","value":"Faris AY","type":"string"},...] |
| contact | whatsapp_number | 6281234567890 |
| contact | section_title | Siap Memulai Project? |
| contact | description | Ceritakan kebutuhan bisnis Anda... |
| contact | cta_label | Konsultasi via WhatsApp |
| contact | response_note | Biasanya balas dalam 1-2 jam |
| seo | page_title | Faris AY \| Faydev -- Software & Web Developer untuk UMKM |
| seo | meta_description | Faris AY adalah Software Engineer... |
| seo | meta_keywords | web developer, software developer, UMKM... |
| seo | og_title | Faris AY \| Faydev -- Software & Web Developer untuk UMKM |
| seo | og_description | Spesialis membangun solusi digital untuk UMKM... |
| seo | og_image | assets/images/uploads/og-image-xyz.jpg |
| footer | brand_tagline | Software & Web Developer untuk UMKM |
| footer | copyright_template | {year} Faris AY -- Faydev. Crafted with love in Indonesia. |

### `skill_groups`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Group ID |
| name | VARCHAR(100) | NOT NULL | Group name (Frontend, Backend, etc.) |
| icon | VARCHAR(120) | NOT NULL | Font Awesome class |
| display_order | INT | NOT NULL, DEFAULT 0 | Sort order |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

### `skills`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Skill ID |
| group_id | INT UNSIGNED | FK -> skill_groups.id, ON DELETE CASCADE | Parent group |
| name | VARCHAR(100) | NOT NULL | Skill name |
| proficiency | TINYINT UNSIGNED | NOT NULL, CHECK (0-100) | Percentage |
| display_order | INT | NOT NULL, DEFAULT 0 | Sort order within group |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

### `services`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Service ID |
| title | VARCHAR(150) | NOT NULL | Service title |
| icon | VARCHAR(120) | NOT NULL | Font Awesome class |
| description | TEXT | NOT NULL | Service description |
| features | TEXT | NOT NULL | JSON array of feature strings |
| cta_url | VARCHAR(255) | NOT NULL | WhatsApp or external URL |
| is_featured | TINYINT(1) | NOT NULL, DEFAULT 0 | Show "Popular" badge |
| badge_text | VARCHAR(50) | NULL | Badge text (e.g., "Popular") |
| display_order | INT | NOT NULL, DEFAULT 0 | Sort order |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

### Existing Tables (Modified)

#### `projects` — Add `display_order` column

| Column | Change |
|---|---|
| display_order | ADD INT NOT NULL DEFAULT 0 |

No breaking changes. Existing columns remain identical.

#### `social_links` — Add `display_order` column

| Column | Change |
|---|---|
| display_order | ADD INT NOT NULL DEFAULT 0 |

No breaking changes.

## 3.3 API Endpoints (Dashboard → DB)

All dashboard API endpoints require an active admin session. Unauthorized requests return `401`.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth.php?action=login` | Authenticate admin |
| POST | `/api/auth.php?action=logout` | Destroy session |
| GET | `/api/hero.php` | Get hero settings |
| POST | `/api/hero.php` | Update hero settings |
| GET | `/api/about.php` | Get about settings |
| POST | `/api/about.php` | Update about settings |
| GET | `/api/skills.php` | Get all skill groups + skills |
| POST | `/api/skills.php?action=create_group` | Create skill group |
| POST | `/api/skills.php?action=update_group` | Update skill group |
| POST | `/api/skills.php?action=delete_group` | Delete skill group (cascades) |
| POST | `/api/skills.php?action=create_skill` | Create skill |
| POST | `/api/skills.php?action=update_skill` | Update skill |
| POST | `/api/skills.php?action=delete_skill` | Delete skill |
| POST | `/api/skills.php?action=reorder` | Batch update display orders |
| GET | `/api/services.php` | Get all services |
| POST | `/api/services.php?action=create` | Create service |
| POST | `/api/services.php?action=update` | Update service |
| POST | `/api/services.php?action=delete` | Delete service |
| POST | `/api/services.php?action=reorder` | Batch update display orders |
| GET | `/api/projects.php` | Get all projects |
| POST | `/api/projects.php?action=create` | Create project |
| POST | `/api/projects.php?action=update` | Update project |
| POST | `/api/projects.php?action=delete` | Delete project(s) |
| GET | `/api/social.php` | Get all social links |
| POST | `/api/social.php?action=create` | Create social link |
| POST | `/api/social.php?action=update` | Update social link |
| POST | `/api/social.php?action=delete` | Delete social link |
| POST | `/api/social.php?action=reorder` | Batch update display orders |
| GET | `/api/contact.php` | Get contact settings |
| POST | `/api/contact.php` | Update contact settings |
| GET | `/api/seo.php` | Get SEO settings |
| POST | `/api/seo.php` | Update SEO settings |
| GET | `/api/footer.php` | Get footer settings |
| POST | `/api/footer.php` | Update footer settings |
| POST | `/api/upload.php` | Upload image file |

### API Response Format

All endpoints follow the existing convention:

```json
// Success
{ "success": true, "data": { ... } }

// Error
{ "success": false, "message": "Error description" }
```

## 3.4 API Endpoints (Landing Page — Public, Read-Only)

New public APIs added to faydev.my.id for sections that are currently hardcoded. These mirror the existing `projects.php` and `social.php` patterns.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/hero.php` | Hero section content |
| GET | `/api/about.php` | About section content |
| GET | `/api/skills.php` | Skill groups + skills |
| GET | `/api/services.php` | Service cards |
| GET | `/api/contact.php` | Contact/CTA content |
| GET | `/api/seo.php` | SEO meta tags |
| GET | `/api/footer.php` | Footer content |

### Fallback Strategy

The landing page `main.js` must implement graceful degradation:
- If any API call fails, the landing page shows the hardcoded HTML defaults (already present in `index.php`)
- The `index.php` file retains its current hardcoded content as fallback markup
- JavaScript replaces hardcoded content only when API returns successfully

## 3.5 Landing Page Modification Strategy

The landing page (`index.php`) keeps its current hardcoded HTML as the **fallback**. JavaScript progressively enhances each section:

```javascript
// Pattern for each section
async function loadHero() {
    try {
        const res = await fetch('api/hero.php');
        const json = await res.json();
        if (json.success) {
            // Replace hardcoded content with DB-driven content
            document.querySelector('.hero-name').innerHTML = `${esc(json.data.name)} <span class="name-accent">${esc(json.data.name_accent)}</span>`;
            // ... etc
        }
        // If API fails, hardcoded HTML remains — graceful degradation
    } catch { /* silent — fallback HTML stays */ }
}
```

This is the **lowest-risk migration path**:
- Zero downtime during deployment
- Landing page works even if the database is empty (fallback content)
- Each section can be migrated independently

## 3.6 Integration Points

| System | Integration | Details |
|---|---|---|
| MySQL | PDO connection | Same DB (`fayd7716_project`), same `includes/db.php` pattern |
| Landing page | Shared database | Dashboard writes to DB, landing page reads via API |
| File system | Image uploads | Dashboard writes to landing page's `assets/images/uploads/` directory |
| WhatsApp | wa.me URL construction | Dashboard stores raw phone number; landing page constructs `https://wa.me/{number}?text={encoded_msg}` |
| Font Awesome | Icon class input | Dashboard validates FA icon classes via regex pattern: `/^fa[sbrl]?\s+fa-[\w-]+$/` |

## 3.7 Security & Privacy

### Authentication
- Passwords hashed with `password_hash()` using `PASSWORD_BCRYPT`
- Session ID regenerated on login (`session_regenerate_id(true)`)
- Sessions stored server-side (default PHP session handler)
- Session timeout: 2 hours of inactivity
- HTTP-only session cookie (`session.cookie_httponly = true`)
- SameSite=Lax cookie attribute

### CSRF Protection
- Every form includes a CSRF token
- Token validated on every POST request
- Token regenerated per-session

### Input Validation
- All user input sanitized server-side before database insertion
- Prepared statements (PDO parameterized queries) for all DB operations — matches existing pattern
- File upload validation: MIME type check, file size limit, filename sanitization
- XSS prevention: `htmlspecialchars()` on output where applicable

### File Upload Security
- Whitelist allowed MIME types: `image/jpeg`, `image/png`, `image/webp`
- Max file size enforced (2MB for profile/OG images, 5MB for project thumbnails)
- Files stored outside PHP execution path if possible, or with `.htaccess` restrictions
- Uploaded filenames randomized (no user-controlled filenames)

### Cross-Origin
- Dashboard API does NOT set `Access-Control-Allow-Origin: *` — session-only access
- Landing page public APIs keep existing `Access-Control-Allow-Origin: *` header

---

# 4. Dashboard UI Design

## 4.1 Design System

The dashboard reuses the landing page's design tokens for brand consistency:

- **Colors**: Same green palette (`--clr-green-400` through `--clr-green-900`)
- **Fonts**: Syne (headings) + DM Sans (body) — same as landing page
- **Theme**: Dark-first with light mode toggle (same as landing page)
- **Border radius**: Same radius tokens (`--radius-sm`, `--radius-md`, `--radius-lg`)

## 4.2 Layout

```
+---------------------------------------------------+
| Sidebar (collapsible)  |  Main Content Area        |
|                        |                           |
| [Logo]                 |  Page Header              |
| Dashboard              |  +-----------+-----------+|
| ─────────              |  | KPI Card  | KPI Card  ||
| Hero                   |  +-----------+-----------+|
| About                  |                           |
| Skills                 |  +---------------------+  |
| Services               |  | Content Form /      |  |
| Projects               |  | Data Table          |  |
| Social Links           |  |                     |  |
| Contact                |  |                     |  |
| SEO                    |  +---------------------+  |
| Footer                 |                           |
| ─────────              |                           |
| [Logout]               |                           |
+---------------------------------------------------+
```

- Sidebar: 240px wide, collapsible to icon-only (60px) on smaller screens
- Main area: Fluid width, max-width 900px for form content
- Mobile: Sidebar becomes a hamburger overlay

## 4.3 Component Patterns

### Form Sections
- Grouped fields in cards with section headers
- Save button fixed at bottom of each form (sticky on scroll)
- Success/error toast notifications after save
- Unsaved changes warning before navigation

### Data Tables (Projects, Skills, Services, Social Links)
- Sortable columns
- Edit/Delete action buttons per row
- Add button at top
- Inline thumbnail preview for projects
- Pagination for projects (10 per page)

### Image Upload
- Drag-and-drop zone with click-to-browse fallback
- Live preview of selected image
- Progress indicator during upload
- File size and type validation client-side (before upload)

---

# 5. Risks & Roadmap

## 5.1 Phased Rollout

### Phase 1 — MVP (Core CRUD)

**Scope**: Auth + Projects CRUD + Social Links CRUD + Dashboard home
**Rationale**: These are the only database-driven sections today. Delivers immediate value with the simplest migration path.

**Deliverables:**
1. Admin login/logout with session management
2. Dashboard home with stats overview
3. Projects management (CRUD + thumbnail upload)
4. Social links management (CRUD + reorder)
5. Dashboard UI shell (sidebar, layout, theme toggle)

**Migration**: None required on landing page — existing APIs work unchanged.

### Phase 2 — Content Sections

**Scope**: Hero + About + Skills + Services + Contact + Footer editors

**Deliverables:**
1. `site_settings` table + seeder with current hardcoded values
2. `skill_groups` + `skills` tables + seeders
3. `services` table + seeder
4. Dashboard editors for each section
5. Landing page public APIs for each section
6. Landing page `main.js` modifications to fetch from APIs with fallback

**Migration**: Run seeder to populate DB with current hardcoded values. Deploy new APIs. Update `main.js`.

### Phase 3 — SEO & Polish

**Scope**: SEO meta editor + Footer editor + UX improvements

**Deliverables:**
1. SEO meta management with character counters
2. Footer content editor
3. Landing page `index.php` modified to render `<head>` meta from DB (PHP server-side, not JS)
4. Unsaved changes warnings
5. Toast notifications
6. Mobile-responsive dashboard polish

## 5.2 Technical Risks

| Risk | Impact | Likelihood | Mitigation |
|---|---|---|---|
| Shared hosting file upload limits | Image uploads may fail if PHP `upload_max_filesize` < 5MB | Medium | Check `php.ini` settings; document required values; compress images client-side before upload |
| Cross-subdomain file writes | Dashboard at panel.faydev.my.id may not have write access to faydev.my.id's `assets/images/` | High | Option A: Both point to same document root's uploads dir. Option B: Dashboard uploads to its own dir, landing page references `panel.faydev.my.id/uploads/`. Option C: Shared hosting addon domain config |
| API latency adding 7+ fetch calls to landing page | Landing page load time may increase beyond 2s target | Medium | Combine all section data into a single `/api/site.php` endpoint returning all sections in one response; implement client-side caching via `localStorage` with TTL |
| Session fixation / hijacking | Admin account compromised | Low | Regenerate session ID on login; HTTP-only cookies; SameSite attribute; optional IP binding |
| Database schema migration on live site | Risk of downtime during ALTER TABLE on `projects` and `social_links` | Low | ALTER TABLE ADD COLUMN is non-breaking on MySQL with DEFAULT values; run during low traffic |
| No .env — hardcoded credentials | DB credentials exposed if repo is public | Medium | Implement `.env` file support (PHP `parse_ini_file` or `vlucas/phpdotenv`), add to `.gitignore` |

## 5.3 Dependencies

| Dependency | Status | Notes |
|---|---|---|
| PHP >= 7.4 with `pdo_mysql`, `gd` (for image resize) | Available | Standard on XAMPP and shared hosting |
| MySQL >= 5.7 | Available | Existing |
| Apache with `mod_rewrite` | Available | Standard on XAMPP/cPanel |
| Font Awesome 6.5.x (CDN) | Available | Already used by landing page |
| Google Fonts (Syne + DM Sans) | Available | Already used by landing page |

**No new external dependencies required.** The dashboard uses the same vanilla PHP + JS stack as the landing page — no Composer, no npm, no build tools.

---

# 6. Deliverables Summary

| # | Deliverable | Phase |
|---|---|---|
| 1 | Database migration SQL (new tables + ALTER existing) | 1 |
| 2 | Admin authentication system (login, session, CSRF) | 1 |
| 3 | Dashboard UI shell (sidebar, layout, theme, responsive) | 1 |
| 4 | Projects CRUD with thumbnail upload | 1 |
| 5 | Social links CRUD with reorder | 1 |
| 6 | Hero section editor | 2 |
| 7 | About section editor | 2 |
| 8 | Skills manager (groups + individual skills) | 2 |
| 9 | Services manager | 2 |
| 10 | Contact/CTA editor | 2 |
| 11 | Landing page public APIs (7 new endpoints) | 2 |
| 12 | Landing page JS modifications (fetch + fallback) | 2 |
| 13 | SEO meta editor | 3 |
| 14 | Footer editor | 3 |
| 15 | Landing page `<head>` server-side rendering from DB | 3 |
| 16 | UX polish (toasts, unsaved warnings, mobile) | 3 |

---

# 7. Complexity Estimate

**Overall**: Medium

| Area | Complexity | Rationale |
|---|---|---|
| Authentication | Low | Single user, PHP sessions, well-established pattern |
| Dashboard UI | Medium | 9 editor pages, responsive sidebar, form validation |
| Projects/Social CRUD | Low | Extends existing tables and patterns |
| Content section APIs | Medium | 7 new API endpoints, key-value store queries |
| Skills/Services CRUD | Medium | Parent-child relationships, reordering logic |
| Image upload/resize | Medium | File handling, GD library, security validation |
| Landing page migration | Medium | Modifying existing JS to fetch from APIs with fallback |
| SEO server-side rendering | Low | PHP include in `<head>`, simple DB query |

**Estimated effort**: ~40-60 hours across all 3 phases.
