# PHPBasePlate V3

A reusable, schema-driven, API-first PHP content platform for building websites and web applications.

Define a content type in JSON. Get admin CRUD, REST API, and Twig template access automatically.

## Stack

| Component | Version |
|---|---|
| PHP | 8.3+ |
| MariaDB / MySQL | 10.4+ / 8+ |
| Twig | 3 |
| Bootstrap | 5.3.3 |
| JavaScript | Vanilla (no framework) |
| Composer | 2+ |
| Web Server | Apache with mod_rewrite (Nginx supported) |

No Node.js runtime is required.

## Features

- **Schema-driven content types** - one JSON file generates admin CRUD + REST API + Twig access
- **14 field types** - text, textarea, richtext, slug, number, boolean, date, datetime, select, image, file, relation, repeater, json
- **Admin panel** - Bootstrap 5 with dynamic sidebar, forms, lists, filters, pagination
- **REST API** - consistent JSON envelope, Bearer token auth, pagination, search, sort, filter
- **Twig site rendering** - content consumed via shared service layer, not HTTP self-calls
- **Authentication & RBAC** - session auth (admin), API token auth, roles, permissions, super-admin bypass
- **Media management** - safe uploads with extension whitelist, image dimensions, date-based storage
- **Settings** - grouped key-value settings with admin editor
- **Menus** - named menus with nested items, Twig rendering helper
- **Revisions** - optional entry revision snapshots on save
- **Security** - CSRF protection, prepared statements, HTML sanitisation, upload restrictions, security headers

## Quick Start

No database required to get started. PHPBasePlate runs on JSON flat files out of the box.

```bash
# 1. Clone and install
git clone https://github.com/ItsMeStevieG/PHPBasePlate.git
cd PHPBasePlate
git checkout v3
composer install

# 2. Configure
cp .env.example .env

# 3. Run setup (seeds JSON data files)
php bin/setup.php

# 4. Start development server
php bin/setup.php

# 6. Start development server
php -S localhost:8000 -t public/
```

| URL | What |
|---|---|
| http://localhost:8000 | Front-end site |
| http://localhost:8000/admin | Admin panel |
| http://localhost:8000/api/health | API health check |

**Default admin:** `admin@phpbaseplate.local` / `admin` (change after first login)

## Storage Drivers

PHPBasePlate supports two storage backends, configured via `STORAGE_DRIVER` in `.env`:

| Driver | Default | Description |
|---|---|---|
| `json` | Yes | JSON flat files in `storage/data/`. No database needed. Works out of the box. |
| `database` | No | MySQL/MariaDB. Full relational storage. Requires database setup and migrations. |
| `auto` | No | Tries database first, falls back to JSON if connection fails. |

### Switching to MySQL

```bash
# 1. Edit .env
STORAGE_DRIVER=database
DB_HOST=localhost
DB_DATABASE=phpbaseplate
DB_USERNAME=root
DB_PASSWORD=your_password

# 2. Create the database
mysql -u root -p -e "CREATE DATABASE phpbaseplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Run setup (runs migrations + seeds)
php bin/setup.php
```

### Auto-failover

Set `STORAGE_DRIVER=auto` to try the database first and automatically fall back to JSON flat files if the database connection fails. Useful for development or resilience.

### Keeping them in sync

JSON files act as a "last known good" snapshot. Use the sync tool to keep them current:

```bash
php bin/sync.php db-to-json   # Snapshot DB to JSON (run after making changes in DB mode)
php bin/sync.php json-to-db   # Push JSON data into DB (run when switching to database)
php bin/sync.php status       # Compare record counts in both stores
```

Setup (`php bin/setup.php`) always seeds JSON files regardless of the active driver, so failover always has baseline data.

## Creating Content Types

Add a JSON file to `resources/schemas/`:

```json
{
    "name": "event",
    "label": "Events",
    "mode": "collection",
    "status_enabled": true,
    "revisioning": false,
    "api": {
        "public_read": true,
        "auth_write": true,
        "allow_delete": false
    },
    "fields": [
        {"name": "title", "label": "Title", "type": "text", "required": true, "searchable": true},
        {"name": "slug", "label": "Slug", "type": "slug", "required": true},
        {"name": "event_date", "label": "Event Date", "type": "date", "required": true, "sortable": true},
        {"name": "body", "label": "Description", "type": "richtext"}
    ]
}
```

Run `php bin/setup.php`. Done - the admin panel, API, and Twig functions now recognise "Events".

## REST API

Every content type gets these endpoints automatically:

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/{type}` | Public | Paginated list |
| GET | `/api/{type}/{slug-or-id}` | Public | Single entry |
| POST | `/api/{type}` | Bearer token | Create entry |
| PUT/PATCH | `/api/{type}/{id}` | Bearer token | Update entry |
| DELETE | `/api/{type}/{id}` | Bearer token | Delete entry |
| POST | `/api/{type}/{id}/publish` | Bearer token | Publish |
| POST | `/api/{type}/{id}/unpublish` | Bearer token | Unpublish |

Query parameters: `page`, `per_page`, `status`, `search`, `sort`, `direction`

Response format:

```json
{
    "success": true,
    "message": "Entries retrieved successfully.",
    "data": [{"id": 1, "type": "news", "title": "...", "slug": "...", "fields": {...}}],
    "meta": {"pagination": {"page": 1, "per_page": 20, "total": 42, "total_pages": 3}},
    "errors": []
}
```

## Twig Functions

Access content through the shared service layer in templates:

```twig
{% set articles = content_list('news', 5) %}
{% set page = content_entry('page', 'about') %}
{% set nav = menu('main') %}
{{ setting('site', 'site_name') }}
{{ entry.payload.body | safe_html }}
{{ entry.published_at | time_ago }}
{{ long_text | excerpt(200) }}
```

## Project Structure

```
PHPBasePlate/
├── app/                    # Application code (PSR-4: ItsMeStevieG\PHPBasePlate\)
│   ├── Admin/              # Admin controllers
│   ├── Api/                # REST API controllers, serializers
│   ├── Auth/               # Authentication, RBAC, middleware
│   ├── Content/            # Schema engine, field types, services
│   ├── Core/               # Kernel, config, routing, database, HTTP, views
│   ├── Frontend/           # Site controllers
│   ├── Media/              # Media management
│   └── Settings/           # Settings and menus
├── bootstrap/              # App bootstrap and global helpers
├── bin/                    # CLI tools (setup, migrate, check)
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # 18 SQL migration files
│   └── seeds/              # 4 PHP seed files
├── docs/                   # Documentation
├── public/                 # Web root (index.php, .htaccess, uploads/)
├── resources/
│   ├── schemas/            # Content type JSON definitions
│   └── views/              # Twig templates (admin, frontend, layouts, partials)
├── routes/                 # Route definitions (web.php, admin.php, api.php)
├── storage/                # Runtime: logs, cache, sessions
└── tests/                  # PHPUnit tests
```

## CLI Tools

```bash
php bin/check.php           # Environment check (PHP, extensions, paths, schemas)
php bin/setup.php           # Full setup: always seeds JSON + DB if configured
php bin/migrate.php migrate # Run database migrations only
php bin/migrate.php seed    # Run database seeds only
php bin/sync.php status     # Show record counts in both JSON and database
php bin/sync.php db-to-json # Snapshot database to JSON (failover backup)
php bin/sync.php json-to-db # Import JSON data into database
```

## Testing

```bash
./vendor/bin/phpunit            # Run all tests
./vendor/bin/phpunit --testdox  # Verbose output
```

## Deployment

### VPS (recommended)

```bash
git clone <repo> /var/www/phpbaseplate
cd /var/www/phpbaseplate && git checkout v3
composer install --no-dev --optimize-autoloader
cp .env.example .env && nano .env    # Set APP_ENV=production, APP_DEBUG=false
php bin/setup.php
# Point Apache/Nginx document root to public/
```

### Shared Hosting

1. Upload project above web root
2. Point domain document root to `public/`
3. Upload `vendor/` if Composer is not available on host
4. Configure `.env` and run `php bin/setup.php`

See [docs/installation.md](docs/installation.md) for Apache/Nginx configs, production checklist, and troubleshooting.

## Documentation

| Document | Description |
|---|---|
| [Installation Guide](docs/installation.md) | Prerequisites, step-by-step setup, server configs, production checklist, troubleshooting |
| [User Guide](docs/user-guide.md) | Content types, field types, admin panel, REST API, Twig functions, CLI tools, extending |

## License

MIT
