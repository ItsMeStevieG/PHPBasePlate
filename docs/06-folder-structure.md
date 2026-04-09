# PHPBasePlate V3 Folder Structure

## 1. Canonical Repository Layout

```text
PHPBasePlate/
├── app/
│   ├── Admin/
│   │   ├── Controllers/
│   │   ├── Forms/
│   │   ├── Tables/
│   │   └── Services/
│   ├── Api/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Serializers/
│   │   └── Services/
│   ├── Auth/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Policies/
│   │   ├── Repositories/
│   │   └── Services/
│   ├── Content/
│   │   ├── FieldTypes/
│   │   ├── Repositories/
│   │   ├── Schema/
│   │   ├── Services/
│   │   ├── Validators/
│   │   └── ValueObjects/
│   ├── Core/
│   │   ├── Config/
│   │   ├── Container/
│   │   ├── Database/
│   │   ├── Exceptions/
│   │   ├── Http/
│   │   ├── Logging/
│   │   ├── Routing/
│   │   ├── Support/
│   │   └── View/
│   ├── Frontend/
│   │   ├── Controllers/
│   │   ├── Presenters/
│   │   └── Services/
│   ├── Media/
│   │   ├── Controllers/
│   │   ├── Repositories/
│   │   └── Services/
│   └── Settings/
│       ├── Controllers/
│       ├── Repositories/
│       └── Services/
├── bootstrap/
│   ├── app.php
│   └── helpers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── paths.php
│   └── view.php
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── schema/
├── docs/
├── public/
│   ├── assets/
│   ├── uploads/
│   ├── .htaccess
│   └── index.php
├── resources/
│   ├── schemas/
│   ├── views/
│   │   ├── admin/
│   │   ├── frontend/
│   │   ├── components/
│   │   ├── layouts/
│   │   └── partials/
│   └── lang/
├── routes/
│   ├── admin.php
│   ├── api.php
│   └── web.php
├── storage/
│   ├── cache/
│   ├── logs/
│   ├── sessions/
│   └── temp/
├── tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
├── vendor/
├── .env.example
├── composer.json
└── README.md
```

## 2. Directory Responsibilities

### 2.1 `app/`
Contains application code grouped by module responsibility rather than flat technical layers alone.

### 2.2 `bootstrap/`
Contains bootstrap files used early in application startup.

### 2.3 `config/`
Contains configuration arrays and environment-aware settings.

### 2.4 `database/`
Contains migrations, seeds, and optional SQL or reference schema files.

### 2.5 `public/`
The only web-accessible document root.

### 2.6 `resources/`
Contains schemas, Twig views, and static source resources.

### 2.7 `routes/`
Contains route declarations split by surface area.

### 2.8 `storage/`
Contains writable files created at runtime.

### 2.9 `tests/`
Contains automated tests.

## 3. Namespace Strategy

The application should use the namespace root:

```php
ItsMeStevieG\PHPBasePlate
```

Examples:

- `ItsMeStevieG\PHPBasePlate\Core\Routing\Router`
- `ItsMeStevieG\PHPBasePlate\Content\Services\EntryService`
- `ItsMeStevieG\PHPBasePlate\Admin\Controllers\ContentController`

## 4. File Naming Rules

- Classes: `StudlyCase.php`
- Config files: `snake_case.php` or grouped names like `database.php`
- Route files: `web.php`, `api.php`, `admin.php`
- Schema files: `news.json`, `pages.json`, `events.json`
- Twig files: `kebab-case.twig` or grouped logically under modules

## 5. Schema Directory Rules

All content schemas should live under:

```text
resources/schemas/
```

Examples:

- `resources/schemas/news.json`
- `resources/schemas/page.json`
- `resources/schemas/event.json`

These files are source-controlled and act as part of the application contract.

## 6. View Directory Rules

### Admin views
`resources/views/admin/`

### Front-end views
`resources/views/frontend/`

### Shared components/partials
`resources/views/components/`
`resources/views/partials/`

### Layouts
`resources/views/layouts/`

## 7. Controller Placement

Controllers should be grouped by module and surface area.

Examples:
- `app/Admin/Controllers/ContentController.php`
- `app/Api/Controllers/ContentApiController.php`
- `app/Frontend/Controllers/PageController.php`

## 8. Service Placement

Business logic belongs in `Services/` inside the owning module.

Examples:
- `app/Content/Services/EntryService.php`
- `app/Auth/Services/AuthService.php`
- `app/Settings/Services/MenuService.php`

## 9. Repository Placement

Persistence logic belongs in `Repositories/`.
These classes should centralise query logic and prevent controllers from becoming SQL-heavy.

## 10. Field Type Placement

Field type implementations belong in:

```text
app/Content/FieldTypes/
```

Examples:
- `TextField.php`
- `SlugField.php`
- `RepeaterField.php`

## 11. Bootstrap Rules

`public/index.php` should remain minimal and should only:
- load Composer autoload
- load bootstrap app
- create request
- dispatch request
- emit response

## 12. Writable Paths

The following paths should be writable by PHP where required:

- `storage/cache`
- `storage/logs`
- `storage/sessions`
- `public/uploads` if public upload serving is used

## 13. Do Not Place Sensitive Files In `public/`

The following must remain outside `public/`:

- source code
- config files
- environment files
- logs
- migration files
- Composer metadata where possible

## 14. Repository Discipline

- Documentation stays in `docs/`
- Schema definitions stay in `resources/schemas/`
- Routing stays in `routes/`
- Runtime-generated files stay in `storage/`
- Publicly served files stay in `public/`

This layout is intended to stay stable over the life of V3.
