# PHPBasePlate V3 Documentation

This folder contains the technical planning documents for rebuilding PHPBasePlate as a modern, reusable, API-first PHP content platform.

## Document Map

1. [01-product-vision.md](01-product-vision.md)
   - Project goals, positioning, principles, and non-goals.

2. [02-prd.md](02-prd.md)
   - Product requirements document covering actors, features, constraints, acceptance criteria, and release scope.

3. [03-system-architecture.md](03-system-architecture.md)
   - Runtime architecture, module boundaries, request lifecycle, service layout, security, and extension model.

4. [04-database-schema.md](04-database-schema.md)
   - Database design for content types, entries, media, users, permissions, menus, settings, and revisions.

5. [05-api-spec.md](05-api-spec.md)
   - REST API conventions, endpoints, payloads, filtering, authentication, and error shapes.

6. [06-folder-structure.md](06-folder-structure.md)
   - Canonical repository layout, namespace rules, coding conventions, and file placement guidance.

7. [07-implementation-plan.md](07-implementation-plan.md)
   - Dependency-aware build plan with phases, milestones, and task ordering.

8. [08-claude-code-execution-guide.md](08-claude-code-execution-guide.md)
   - Guidance for agentic implementation in Claude Code, including branch strategy, task breakdown, and done criteria.

9. [09-deployment-and-environments.md](09-deployment-and-environments.md)
   - Deployment model for shared hosting and VPS, environment handling, release process, and operational checks.

10. [10-installation-guide.md](10-installation-guide.md)
    - Step-by-step installation, Apache/Nginx configuration, production checklist, and troubleshooting.

11. [11-user-guide.md](11-user-guide.md)
    - Content types, field types, admin panel, REST API, Twig functions, CLI tools, and extending the platform.

## Stack Lock

PHPBasePlate V3 is intentionally designed around the following stack:

- PHP 8.3+
- MariaDB 10.4+ or MySQL 8+
- Twig
- Bootstrap 5.3.3
- Vanilla JavaScript
- Composer
- Apache with mod_rewrite

No Node.js runtime is required in production.

## Core Direction

PHPBasePlate V3 is not intended to be a clone of October CMS or Strapi.
It is intended to be a lightweight, reusable, API-first PHP platform that supports:

- classic server-rendered Twig sites
- headless JSON delivery
- schema-driven content types
- reusable admin UI generation
- easy deployment to standard PHP hosting or VPS environments

## Build Priority

The first production-capable version should prioritise:

1. Stable core bootstrap and routing
2. Authentication and RBAC
3. Schema-driven content types
4. CRUD admin generation
5. JSON API delivery
6. Twig consumption from shared services
7. Media handling
8. Settings and menu management
9. Revisions and publishing workflow

## Working Rule

All implementation work should follow the dependency-aware order in `07-implementation-plan.md`.
No feature work should bypass the core contracts defined in these documents.
