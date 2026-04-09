# PHPBasePlate V3 - User Guide

## Overview

PHPBasePlate V3 is a schema-driven content platform. You define content types in JSON files, and the system automatically generates:

- Admin CRUD screens (list, create, edit, delete, publish)
- REST API endpoints (list, show, create, update, delete, publish)
- Twig template access via helper functions

## Content Types

### Creating a Content Type

Create a JSON file in `resources/schemas/`:

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
    "admin": {
        "icon": "bi-calendar-event",
        "nav_group": "Content"
    },
    "fields": [
        {
            "name": "title",
            "label": "Title",
            "type": "text",
            "required": true,
            "searchable": true,
            "sortable": true,
            "config": { "max_length": 255 }
        },
        {
            "name": "slug",
            "label": "Slug",
            "type": "slug",
            "required": true
        },
        {
            "name": "event_date",
            "label": "Event Date",
            "type": "date",
            "required": true,
            "sortable": true
        },
        {
            "name": "body",
            "label": "Description",
            "type": "richtext",
            "required": false,
            "searchable": true
        },
        {
            "name": "featured",
            "label": "Featured",
            "type": "boolean",
            "filterable": true
        }
    ]
}
```

After creating the file, run:

```bash
php bin/setup.php
```

The admin panel, API, and Twig helpers will automatically recognise the new type.

### Schema Options

| Key | Type | Description |
|---|---|---|
| `name` | string | Machine name (lowercase, underscores). Must be unique. |
| `label` | string | Human-readable display name |
| `mode` | string | `collection` (multiple entries) or `single` (one entry) |
| `status_enabled` | bool | Enable draft/published/archived workflow |
| `revisioning` | bool | Save revision snapshots on create/update |
| `api.public_read` | bool | Allow unauthenticated API reads |
| `api.auth_write` | bool | Allow token-authenticated API writes |
| `api.allow_delete` | bool | Allow API delete operations |
| `admin.icon` | string | Bootstrap Icons class for sidebar |
| `admin.nav_group` | string | Sidebar section label |

### Field Types

| Type | Widget | Description |
|---|---|---|
| `text` | Text input | Single-line text. Config: `max_length` |
| `textarea` | Textarea | Multi-line plain text |
| `richtext` | Textarea | HTML rich text (editor enhancement planned) |
| `slug` | Text input | URL-safe slug, auto-generated from title if blank |
| `number` | Number input | Numeric value. Config: `min`, `max`, `step` |
| `boolean` | Checkbox toggle | True/false value |
| `date` | Date picker | Date (YYYY-MM-DD) |
| `datetime` | Datetime picker | Date and time |
| `select` | Dropdown | Single selection. Config: `options` array |
| `image` | Number input | Media file ID (media picker planned) |
| `file` | Number input | Media file ID |
| `relation` | - | Link to other content entries |
| `repeater` | JSON textarea | Array of repeated structured data |
| `json` | JSON textarea | Arbitrary JSON data |

### Field Options

| Key | Type | Description |
|---|---|---|
| `name` | string | Machine name for storage |
| `label` | string | Display label |
| `type` | string | One of the field types above |
| `required` | bool | Validation: field must have a value |
| `searchable` | bool | Include in search queries |
| `filterable` | bool | Show as a filter option in admin |
| `sortable` | bool | Allow sorting by this field |
| `config` | object | Type-specific configuration |

---

## Admin Panel

Access at `/admin`. Login required.

### Dashboard
Shows content type cards with quick links to manage and create entries.

### Content Management
For each content type:
- **List view**: Paginated table with search, status filter, sort. Action buttons for edit, publish/unpublish, delete.
- **Create**: Form generated from schema fields. Two-column layout with fields on the left, publishing options on the right.
- **Edit**: Pre-populated form with current values. Includes metadata (created, updated, published dates).

### Media Library
Upload and manage files at `/admin/media`. Supports images, documents, and common file types. Files stored in `public/uploads/` with date-based subdirectories and randomised filenames.

### Settings
Grouped settings at `/admin/settings`. Default group: "Site Settings" with site name, tagline, email, SEO defaults, footer text, and maintenance mode.

### Menus
Menu management at `/admin/menus`. Add, reorder, and remove menu items. Each item has a label, type (internal/external), and URL.

---

## REST API

### Base URL

```
/api
```

### Response Format

All responses use a consistent JSON envelope:

```json
{
    "success": true,
    "message": "Description of what happened.",
    "data": [],
    "meta": {
        "pagination": {
            "page": 1,
            "per_page": 20,
            "total": 42,
            "total_pages": 3
        }
    },
    "errors": []
}
```

### Health Check

```
GET /api/health
```

### Content Endpoints

For any registered content type (e.g., `page`, `news`):

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/{type}` | Public | List entries (paginated) |
| GET | `/api/{type}/{slug-or-id}` | Public | Get single entry |
| POST | `/api/{type}` | Token | Create entry |
| PUT/PATCH | `/api/{type}/{id}` | Token | Update entry |
| DELETE | `/api/{type}/{id}` | Token | Delete entry (if allowed) |
| POST | `/api/{type}/{id}/publish` | Token | Publish entry |
| POST | `/api/{type}/{id}/unpublish` | Token | Unpublish entry |

### Query Parameters (GET list)

| Param | Description |
|---|---|
| `page` | Page number (default: 1) |
| `per_page` | Items per page (default: 20, max: 100) |
| `status` | Filter by status: `draft`, `published`, `archived` |
| `search` | Search title and summary |
| `sort` | Sort field: `id`, `title`, `slug`, `status`, `created_at`, `updated_at`, `published_at` |
| `direction` | Sort direction: `asc` or `desc` |

**Note:** Public (unauthenticated) requests only see `published` entries.

### Authentication

Write endpoints require a Bearer token:

```
Authorization: Bearer {your-api-token}
```

API tokens are generated via `ApiTokenService::generate()` and stored as SHA-256 hashes. Tokens can have scopes and expiration dates.

---

## Twig Templates

### Available Functions

```twig
{# Fetch a list of published entries #}
{% set articles = content_list('news', 5) %}

{# Fetch a single entry by slug #}
{% set page = content_entry('page', 'about-us') %}

{# Get all registered content types #}
{% set types = content_types() %}

{# Get a menu by machine name (returns nested tree) #}
{% set nav = menu('main') %}

{# Get a single setting #}
{{ setting('site', 'site_name') }}

{# Get all settings in a group #}
{% set site = settings_group('site') %}

{# Get a config value #}
{{ config('app.name') }}

{# Generate asset URL #}
{{ asset('css/style.css') }}

{# Generate named route URL #}
{{ route('news.show', {slug: 'my-article'}) }}
```

### Available Filters

```twig
{# Strip HTML and truncate #}
{{ entry.payload.body | excerpt(200) }}

{# Human-readable relative time #}
{{ entry.published_at | time_ago }}

{# Sanitised HTML output (for richtext fields) #}
{{ entry.payload.body | safe_html }}
```

### Menu Rendering Example

```twig
{% set items = menu('main') %}
<nav>
    <ul class="nav">
        {% for item in items %}
        <li class="nav-item">
            <a href="{{ item.url }}" class="nav-link"
               {% if item.target %}target="{{ item.target }}"{% endif %}>
                {{ item.label }}
            </a>
            {% if item.children %}
            <ul>
                {% for child in item.children %}
                <li><a href="{{ child.url }}">{{ child.label }}</a></li>
                {% endfor %}
            </ul>
            {% endif %}
        </li>
        {% endfor %}
    </ul>
</nav>
```

---

## CLI Tools

### Environment Check

```bash
php bin/check.php
```

Checks PHP version, extensions, paths, writable directories, config files, and schemas.

### Full Setup

```bash
php bin/setup.php
```

Runs migrations, seeders, and syncs schemas to the database.

### Migrations Only

```bash
php bin/migrate.php migrate
```

### Seeds Only

```bash
php bin/migrate.php seed
```

### Both

```bash
php bin/migrate.php all
```

---

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run with verbose output
./vendor/bin/phpunit --testdox

# Run a specific test suite
./vendor/bin/phpunit --testsuite Unit
```

Test coverage:
- Core: Container, Config, Router, Request, Response
- Content: SchemaValidator, FieldTypeRegistry, EntryValidator, Slug/Str helpers

---

## Extending the Platform

### Adding a Frontend Controller

1. Create `app/Frontend/Controllers/EventController.php`
2. Inject `EntryService` via the container
3. Call `$entryService->list()` or `$entryService->findBySlug()`
4. Render a Twig template with the data
5. Register routes in `routes/web.php`

### Adding a Field Type

1. Create `app/Content/FieldTypes/MyField.php` implementing `FieldTypeInterface`
2. Register it in `FieldTypeRegistry::createDefault()`
3. Add rendering case in `resources/views/admin/partials/field.twig`

### Adding Middleware

1. Create a class implementing `MiddlewareInterface`
2. Register in the App kernel container
3. Apply to routes via `->middleware([YourMiddleware::class])`
