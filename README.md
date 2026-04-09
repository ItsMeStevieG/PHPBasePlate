# PHPBasePlate V3

A reusable, API-first PHP content platform for building websites and web applications.

## Stack

- PHP 8.3+
- MariaDB 10.4+ / MySQL 8+
- Twig 3
- Bootstrap 5.3.3
- Vanilla JavaScript
- Composer
- Apache with mod_rewrite

## Features

- **Schema-driven content types** - define a JSON schema, get admin CRUD + REST API + Twig access automatically
- **Generated admin panel** - Bootstrap 5 admin with dynamic forms, lists, filters, and pagination
- **REST API** - consistent JSON envelope, token auth for writes, public reads
- **Twig site rendering** - consume content through shared services, not HTTP self-calls
- **Authentication & RBAC** - session auth for admin, API token auth, roles and permissions
- **Media management** - upload, metadata, safe storage
- **Settings & menus** - grouped settings, nested menu management
- **Revisions** - optional entry revision history

## Quick Start

```bash
# Clone and install
git clone https://github.com/ItsMeStevieG/PHPBasePlate.git
cd PHPBasePlate
git checkout v3
composer install

# Configure
cp .env.example .env
# Edit .env with your database credentials

# Run environment check
php bin/check.php

# Run setup (migrations + seeds + schema sync)
php bin/setup.php

# Start development server
php -S localhost:8000 -t public/
```

Then visit:
- **Site**: http://localhost:8000
- **Admin**: http://localhost:8000/admin
- **API**: http://localhost:8000/api/health

Default admin: `admin@phpbaseplate.local` / `admin`

## Project Structure

```
PHPBasePlate/
├── app/                    # Application code (PSR-4: ItsMeStevieG\PHPBasePlate\)
│   ├── Admin/              # Admin controllers, forms, tables
│   ├── Api/                # REST API controllers, serializers
│   ├── Auth/               # Authentication, RBAC, middleware
│   ├── Content/            # Schema engine, field types, services
│   ├── Core/               # Kernel, config, routing, database, HTTP
│   ├── Frontend/           # Site controllers
│   ├── Media/              # Media management
│   └── Settings/           # Settings and menus
├── bootstrap/              # App bootstrap and helpers
├── config/                 # Configuration files
├── database/               # Migrations and seeds
├── docs/                   # Planning documents
├── public/                 # Web root (index.php, .htaccess, uploads)
├── resources/
│   ├── schemas/            # Content type JSON definitions
│   └── views/              # Twig templates
├── routes/                 # Route definitions (web, admin, api)
├── storage/                # Logs, cache, sessions
└── tests/                  # PHPUnit tests
```

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
        {"name": "date", "label": "Event Date", "type": "date", "required": true},
        {"name": "body", "label": "Description", "type": "richtext", "required": false}
    ]
}
```

Then run `php bin/setup.php` to sync. The admin CRUD and API endpoints are generated automatically.

## API

All content types get REST endpoints:

| Method | Path | Auth | Purpose |
|---|---|---|---|
| GET | `/api/{type}` | Public | Paginated list |
| GET | `/api/{type}/{slug}` | Public | Single entry |
| POST | `/api/{type}` | Token | Create |
| PUT | `/api/{type}/{id}` | Token | Update |
| DELETE | `/api/{type}/{id}` | Token | Delete |

Response format:
```json
{
    "success": true,
    "message": "Entries retrieved successfully.",
    "data": [...],
    "meta": {"pagination": {"page": 1, "per_page": 20, "total": 5, "total_pages": 1}},
    "errors": []
}
```

## Twig Functions

Use in templates to access content via the shared service layer:

```twig
{% set pages = content_list('page', 5) %}
{% set entry = content_entry('news', 'my-article') %}
{% set items = menu('main') %}
{% set name = setting('site', 'site_name') %}
{{ 'Hello World' | excerpt(20) }}
{{ entry.published_at | time_ago }}
```

## Deployment

### VPS

```bash
git clone <repo> /var/www/phpbaseplate
cd /var/www/phpbaseplate && git checkout v3
composer install --no-dev --optimize-autoloader
cp .env.example .env && nano .env
php bin/setup.php
# Point Apache/Nginx document root to public/
```

### Shared Hosting

1. Upload project above web root
2. Point domain document root to `public/`
3. Upload `vendor/` if Composer unavailable on host
4. Configure `.env` and run `php bin/setup.php`

## Testing

```bash
./vendor/bin/phpunit
```

## Documentation

See `docs/` for detailed planning documents:
- Product vision, PRD, system architecture
- Database schema, API spec, folder structure
- Implementation plan, execution guide, deployment

## License

MIT
