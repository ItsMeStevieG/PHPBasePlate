# PHPBasePlate V3 Implementation Plan

## 1. Execution Strategy

Implementation must be dependency-aware and phase-based.
The build order matters.

The guiding rule is:

> build the stable platform substrate first, then the reusable domain engine, then the UI and delivery surfaces, then polish and operational tooling.

## 2. Phase Overview

### Phase 0 - Project Reset and Baseline
Goal: establish the new V3 branch, documentation, and project conventions.

### Phase 1 - Core Kernel
Goal: bootstrap, config, routing, middleware, request/response handling, container, and database access.

### Phase 2 - Auth and Security
Goal: sessions, login, roles, permissions, CSRF, and protected routes.

### Phase 3 - Schema and Content Engine
Goal: schema loading, field registry, validation, content type registration, and entry persistence.

### Phase 4 - Admin CRUD
Goal: generated forms, lists, edit screens, and content operations.

### Phase 5 - API Delivery
Goal: stable REST API endpoints with serialisation and filtering.

### Phase 6 - Twig Integration and Site Layer
Goal: shared service consumption in server-rendered pages.

### Phase 7 - Supporting Features
Goal: media, menus, settings, revisions, publishing workflow.

### Phase 8 - Hardening and Packaging
Goal: testing, deployment docs, install flow, seed data, and release readiness.

## 3. Dependency Rules

### Rule 1
Do not build admin CRUD screens before the schema loader, validators, and content persistence contracts exist.

### Rule 2
Do not build protected API writes before auth, tokens, and permission checks exist.

### Rule 3
Do not let Twig consume raw repositories directly; Twig must consume services or presenters.

### Rule 4
Do not create content-specific business code before the generic content engine is stable.

## 4. Detailed Task Breakdown

## Phase 0 - Project Reset and Baseline

- [ ] Keep all V3 work on the `v3` branch.
- [ ] Preserve or archive old implementation files only after replacement strategy is clear.
- [ ] Add `.env.example` and document required environment variables.
- [ ] Define namespace root and PSR-4 mapping in `composer.json`.
- [ ] Set coding conventions for directories, naming, and module boundaries.

**Done when:** the repo is ready to accept the new application structure without ambiguity.

## Phase 1 - Core Kernel

### 1.1 Bootstrap
- [ ] Create `public/index.php` front controller.
- [ ] Create `bootstrap/app.php`.
- [ ] Load Composer autoload and bootstrap helpers.

### 1.2 Config and Env
- [ ] Implement env loader.
- [ ] Create config repository/manager.
- [ ] Add `config/app.php`, `config/database.php`, `config/view.php`, `config/auth.php`.

### 1.3 HTTP Foundation
- [ ] Implement request abstraction.
- [ ] Implement response abstraction.
- [ ] Implement redirect response helper.
- [ ] Implement JSON response helper.

### 1.4 Routing
- [ ] Implement route registration API.
- [ ] Implement route dispatcher.
- [ ] Load `routes/web.php`, `routes/admin.php`, `routes/api.php`.

### 1.5 Middleware Pipeline
- [ ] Build middleware contract.
- [ ] Build middleware runner.
- [ ] Support route-level and global middleware.

### 1.6 Database Layer
- [ ] Implement PDO-based connection manager.
- [ ] Implement base query/repository helpers.
- [ ] Implement transaction helper.

### 1.7 Error Handling and Logging
- [ ] Central exception handler.
- [ ] Dev vs production error display modes.
- [ ] File logger to `storage/logs`.

**Done when:** a request can be routed through middleware to a controller and return HTML or JSON with config and DB access available.

## Phase 2 - Auth and Security

### 2.1 Sessions and Login
- [ ] Implement session bootstrap.
- [ ] Implement login form and controller.
- [ ] Implement logout.
- [ ] Implement session regeneration.

### 2.2 Users and Roles
- [ ] Create migrations for users, roles, permissions, pivots.
- [ ] Build repositories/services for auth and RBAC.

### 2.3 Middleware
- [ ] Auth-required middleware.
- [ ] Guest-only middleware.
- [ ] Permission/role middleware.
- [ ] CSRF verification middleware.

### 2.4 API Tokens
- [ ] Token generation service.
- [ ] Token hashing and verification.
- [ ] API auth middleware.

**Done when:** admin routes and protected API routes are enforced correctly.

## Phase 3 - Schema and Content Engine

### 3.1 Schema System
- [ ] Implement schema discovery from `resources/schemas`.
- [ ] Implement schema parser.
- [ ] Implement schema validation.
- [ ] Implement content type registry.

### 3.2 Field Registry
- [ ] Create field type contract.
- [ ] Implement initial field types.
- [ ] Implement field-specific validation mapping.

### 3.3 Content Persistence
- [ ] Create migrations for content tables.
- [ ] Build content repositories.
- [ ] Build entry service create/update/delete methods.
- [ ] Implement slug generation rules.
- [ ] Implement status and publish workflow basics.

### 3.4 Revisions
- [ ] Implement revision snapshot write on save.
- [ ] Implement revision fetch and restore hooks later.

**Done when:** a schema file can define a type and entries can be persisted using generic services.

## Phase 4 - Admin CRUD

### 4.1 Admin Shell
- [ ] Create admin layout with Bootstrap 5.3.3.
- [ ] Create admin navigation.
- [ ] Create flash messaging and validation display components.

### 4.2 Generated Lists
- [ ] Build list/table generator from schema metadata.
- [ ] Support sortable columns where configured.
- [ ] Support search and status filters.

### 4.3 Generated Forms
- [ ] Build form renderer from field definitions.
- [ ] Handle create and edit modes.
- [ ] Handle field hydration and old input display.

### 4.4 Entry Operations
- [ ] Create entry create action.
- [ ] Create entry update action.
- [ ] Create delete action.
- [ ] Create publish/unpublish actions.

**Done when:** content types can be fully managed in the admin UI without custom CRUD coding.

## Phase 5 - API Delivery

### 5.1 Route Registration
- [ ] Auto-register content API routes based on schema API config.

### 5.2 Serialisation
- [ ] Create content entry serializer.
- [ ] Create settings serializer.
- [ ] Create menu serializer.

### 5.3 Query Handling
- [ ] Implement pagination parser.
- [ ] Implement filter parser.
- [ ] Implement search support.
- [ ] Implement approved sort handling.

### 5.4 Protected Writes
- [ ] Gate writes by token scopes and permissions.

**Done when:** the API can serve consistent list and single-item responses, plus protected write actions.

## Phase 6 - Twig Integration and Site Layer

### 6.1 Twig Setup
- [ ] Configure Twig environment.
- [ ] Register global helpers where appropriate.
- [ ] Add custom Twig functions/filters for menus, settings, and content access.

### 6.2 Front-End Consumption
- [ ] Create service-backed front-end controllers.
- [ ] Build example pages consuming content internally.
- [ ] Confirm no self-HTTP API calls are used.

**Done when:** a server-rendered site can consume content through the same service layer used by the API and admin.

## Phase 7 - Supporting Features

### 7.1 Media
- [ ] Upload forms and endpoints.
- [ ] File validation.
- [ ] Metadata persistence.
- [ ] Entry-media linking.

### 7.2 Settings
- [ ] Settings group CRUD.
- [ ] Settings item rendering and persistence.

### 7.3 Menus
- [ ] Menu and menu item CRUD.
- [ ] Nested item ordering.
- [ ] Twig menu rendering helper.

### 7.4 Revisions and Audit
- [ ] Revision history UI.
- [ ] Optional activity log UI.

**Done when:** supporting platform features work end-to-end.

## Phase 8 - Hardening and Packaging

### 8.1 Testing
- [ ] Unit tests for schema parsing and services.
- [ ] Feature tests for auth and API.
- [ ] Integration tests for content CRUD.

### 8.2 Installer/Setup
- [ ] Add migration runner.
- [ ] Add seed runner.
- [ ] Add first-admin setup flow or CLI.

### 8.3 Deployment
- [ ] Document shared hosting deployment.
- [ ] Document VPS deployment.
- [ ] Add writable path checks.
- [ ] Add environment validation checks.

### 8.4 Release Readiness
- [ ] Confirm demo content type works.
- [ ] Confirm admin, API, and Twig site integration all work.
- [ ] Tag V3 MVP candidate.

**Done when:** the project can be installed, configured, and demonstrated from a clean environment.

## 5. Suggested First Content Types For Dogfooding

To validate the engine, build these early sample schemas:
- page
- news
- menu
- site settings

These give broad enough coverage to test real-world content behaviour.

## 6. Implementation Order Summary

Strict order:

1. bootstrap
2. config/env
3. request/response/router/middleware
4. database layer
5. auth and RBAC
6. schema loader and field registry
7. content repositories and services
8. admin shell and generated CRUD
9. API layer and serializers
10. Twig site consumption
11. media, menus, settings, revisions
12. tests and packaging

## 7. Anti-Patterns To Avoid During Build

- building hard-coded CRUD before generic content services
- writing SQL directly inside controllers or Twig templates
- making Twig pages consume the internal API over HTTP
- mixing admin presentation logic into domain services
- adding advanced features before the core content engine is stable
