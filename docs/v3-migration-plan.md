# PHPBasePlate v3 Migration Plan

## Overview

Complete rewrite of PHPBasePlate from a v2 procedural/basic-OOP framework to a modern PHP 8.1+ framework with PSR standards, Composer, namespaces, and proper architecture.

## Phase 1: Foundation

**Goal:** Set up project skeleton, Composer, PSR-4 autoloading, and core bootstrap.

### Tasks
- [x] Backup v2 to `v2` branch
- [x] Create CLAUDE.md and planning docs
- [ ] Create `composer.json` with PSR-4 autoloading (`PHPBasePlate\` -> `src/`)
- [ ] Create directory structure (`public/`, `src/`, `config/`, `templates/`, `storage/`, `tests/`)
- [ ] Create `public/index.php` as single entry point
- [ ] Implement `Core\App` - application kernel (bootstrap, service container, run)
- [ ] Implement `Core\Config` - configuration loader (PHP arrays + `.env` support)
- [ ] Implement `Core\Container` - simple PSR-11 dependency injection container
- [ ] Create `.env.example` with default configuration values
- [ ] Create `config/app.php`, `config/database.php` configuration files

## Phase 2: HTTP Layer

**Goal:** Request/Response abstraction and routing.

### Tasks
- [ ] Implement `Http\Request` - wraps `$_GET`, `$_POST`, `$_SERVER`, `$_FILES`
- [ ] Implement `Http\Response` - status codes, headers, body, JSON responses
- [ ] Implement `Core\Router` - route registration, pattern matching, named routes
- [ ] Implement `Http\Middleware\MiddlewareInterface` and pipeline
- [ ] Create basic middleware: CSRF protection, session handling

## Phase 3: Template Engine (BluePrint v3)

**Goal:** Modernize BluePrint while keeping `[@tag]` syntax.

### Tasks
- [ ] Implement `Template\BluePrint` with:
  - `[@tag]` variable replacement (v2 compat)
  - Template inheritance (`[@extends('layout')]`)
  - Sections/blocks (`[@section('content')]...[@endsection]`)
  - Includes (`[@include('partial')]`)
  - Simple conditionals (`[@if $var]...[@endif]`)
  - Loops (`[@foreach $items as $item]...[@endforeach]`)
  - Auto-escaping by default (raw output via `[@raw:tag]`)
- [ ] Template caching for compiled templates
- [ ] Template directory configuration

## Phase 4: Database Layer

**Goal:** Replace OOPSQL with a secure, modern database layer.

### Tasks
- [ ] Implement `Database\Connection` - PDO wrapper (replaces mysqli), connection pooling
- [ ] Implement `Database\QueryBuilder` - fluent query builder with prepared statements
- [ ] Implement `Database\Model` - base model class (replaces `table` class)
  - Property mapping, CRUD operations, relationships
- [ ] Implement `Database\Migration` - simple schema migration system
- [ ] Implement `Database\Seeder` - database seeding

## Phase 5: Error Handling (Blunder v3)

**Goal:** Modern error handling with PSR-3 compatible logging.

### Tasks
- [ ] Implement `Error\Blunder` - exception handler, error-to-exception conversion
- [ ] Implement `Error\Logger` - PSR-3 compatible file logger
- [ ] Pretty error pages for development mode
- [ ] Clean error pages for production mode
- [ ] Log rotation and configurable log levels

## Phase 6: Support Utilities

**Goal:** Modernize ToolChest and TimeMachine.

### Tasks
- [ ] Implement `Support\ToolChest` - debug helpers (`dd()`, `dump()`, `env()`)
- [ ] Implement `Support\TimeMachine` - timezone utilities (modernized with DateTimeImmutable)
- [ ] Implement `Support\Str` - string helpers
- [ ] Implement `Support\Arr` - array helpers
- [ ] Global helper functions (`config()`, `env()`, `view()`, `route()`, `dd()`)

## Phase 7: Installer

**Goal:** Modern web-based installer that generates `.env` files.

### Tasks
- [ ] Create installer route/controller
- [ ] System requirements check (PHP version, extensions, permissions)
- [ ] Database connection test
- [ ] `.env` file generation
- [ ] Initial migration execution
- [ ] Modern UI (no Bootstrap 3 dependency)

## Phase 8: Testing & Polish

**Goal:** Test coverage, documentation, and release readiness.

### Tasks
- [ ] PHPUnit test suite setup
- [ ] Unit tests for Core components
- [ ] Unit tests for Database layer
- [ ] Unit tests for Template engine
- [ ] Integration tests for HTTP layer
- [ ] PHPStan static analysis (level 6+)
- [ ] Update README.md with v3 documentation
- [ ] Create example application
