# PHPBasePlate V3 - Claude Code Instructions

## Purpose

This repository is being rebuilt as **PHPBasePlate V3**, a reusable, API-first PHP content platform.

Claude Code must treat the `docs/` directory as the primary project contract.

## Mandatory Reading Order

Before making architectural changes, read these documents in order:

1. `docs/README.md`
2. `docs/01-product-vision.md`
3. `docs/02-prd.md`
4. `docs/03-system-architecture.md`
5. `docs/04-database-schema.md`
6. `docs/05-api-spec.md`
7. `docs/06-folder-structure.md`
8. `docs/07-implementation-plan.md`
9. `docs/08-claude-code-execution-guide.md`
10. `docs/09-deployment-and-environments.md`

## Stack Lock

Use only this stack unless the project owner explicitly changes it:

- PHP 8.3+
- MariaDB/MySQL
- Twig
- Bootstrap 5.3.3
- Vanilla JavaScript
- Composer
- Apache

Do not introduce Laravel, Symfony full-stack, React, Vue, Next.js, Node.js runtime, or other heavy framework assumptions.

## Core Rules

1. Stay on the `v3` branch unless explicitly instructed otherwise.
2. Follow the dependency-aware order in `docs/07-implementation-plan.md`.
3. Keep controllers thin.
4. Keep Twig presentational.
5. Put business logic in services.
6. Put persistence logic in repositories.
7. Do not make internal HTTP calls from Twig pages to the app’s own API.
8. Keep the app deployable on a standard PHP stack without a Node runtime.

## Architecture Rules

- `public/` is the only document root.
- `resources/schemas/` contains content schemas.
- `routes/` contains route definitions.
- `storage/` contains runtime-generated writable files.
- `app/` contains namespaced application code grouped by module.

## Build Priority

The next implementation priorities are:

1. front controller and bootstrap
2. config and env loader
3. request/response abstractions
4. router and middleware pipeline
5. database layer
6. auth and RBAC
7. schema-driven content engine
8. generated admin CRUD
9. REST API
10. Twig site integration

## Definition of Done

A work slice is done only when:

- code is in the correct module directory
- naming is consistent
- the slice is coherent and testable
- syntax is valid
- docs are updated if the design changed materially

## Working Style

Prefer small coherent changes over huge unfocused edits.
Refactor architecture when needed instead of layering hacks over mistakes.

## Initial Goal

The immediate goal is to establish a stable V3 foundation that can:

- boot from `public/index.php`
- load config and env values
- route requests
- render a Twig page
- return JSON responses
- provide a clear base for the content engine
