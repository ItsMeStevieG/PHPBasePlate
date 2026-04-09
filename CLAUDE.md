# PHPBasePlate v3 - Claude Code Instructions

## Project Overview

PHPBasePlate is a lightweight PHP framework for building portable websites and web applications. v3 is a complete rewrite modernizing the framework with PSR standards, Composer support, proper namespacing, and a modern architecture while preserving the core philosophy: simplicity and portability.

## Architecture

```
PHPBasePlate/
├── public/              # Web root (index.php entry point)
├── src/                 # PSR-4 autoloaded source code
│   ├── Core/            # Framework kernel (App, Router, Config, Container)
│   ├── Database/        # Database layer (Connection, QueryBuilder, Model)
│   ├── Http/            # Request/Response objects, Middleware
│   ├── Template/        # Template engine (BluePrint v3)
│   ├── Error/           # Error handling (Blunder v3)
│   ├── Support/         # Utilities (ToolChest v3, TimeMachine v3)
│   └── Install/         # Installer module
├── config/              # Configuration files (app.php, database.php, etc.)
├── templates/           # Template/view files
├── resources/           # Static assets (css, js, images)
├── storage/             # Logs, cache, sessions
├── tests/               # PHPUnit tests
├── docs/                # Planning and documentation
├── composer.json        # Dependencies and PSR-4 autoloading
└── .env.example         # Environment variable template
```

## Key Design Decisions

- **PHP 8.1+** minimum requirement
- **PSR-4** autoloading via Composer
- **PSR-7** compatible HTTP message interfaces
- **PSR-11** compatible dependency injection container
- **PSR-15** middleware support
- **No heavy dependencies** - framework stays lightweight
- **Environment-based config** using `.env` files (replaces LOC switcher)
- **Prepared statements only** for all database queries (replaces string interpolation)
- **Named routes** with a simple router

## v2 to v3 Component Mapping

| v2 Component | v3 Component | Namespace |
|---|---|---|
| config.php (Konstruct) | Config + .env loader | `PHPBasePlate\Core\Config` |
| common.php | App bootstrap + Container | `PHPBasePlate\Core\App` |
| BluePrint | BluePrint v3 (template engine) | `PHPBasePlate\Template\BluePrint` |
| Blunder | Blunder v3 (error handler) | `PHPBasePlate\Error\Blunder` |
| OOPSQL + table | Connection + QueryBuilder + Model | `PHPBasePlate\Database\*` |
| ToolChest | ToolChest v3 (utilities) | `PHPBasePlate\Support\ToolChest` |
| TimeMachine | TimeMachine v3 (timezone) | `PHPBasePlate\Support\TimeMachine` |

## Development Commands

```bash
# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Run linter/static analysis
./vendor/bin/phpstan analyse src/

# Start dev server
php -S localhost:8000 -t public/
```

## Code Standards

- Follow **PSR-12** coding style
- Use **strict types** (`declare(strict_types=1)`) in all PHP files
- Type-hint all method parameters and return types
- Use constructor promotion where appropriate (PHP 8.1+)
- Template tags use the `[@TagName]` syntax (carried over from v2 BluePrint)
- Database queries must use prepared statements - never interpolate user input into SQL
- All classes must be in the `PHPBasePlate` namespace

## Important Notes

- The `v2` branch contains the original codebase for reference
- The `master` branch will become v3 once complete
- Keep the framework lightweight - avoid unnecessary dependencies
- Maintain backwards compatibility for the `[@tag]` template syntax
- The installer should generate `.env` files instead of editing `config.php`
