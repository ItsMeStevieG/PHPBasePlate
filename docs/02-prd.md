# PHPBasePlate V3 Product Requirements Document

## 1. Product Summary

PHPBasePlate V3 is a reusable PHP content platform designed to support both classic server-rendered websites and API-driven content delivery.

It must enable a developer to define content structures once, then automatically gain:

- database-aware storage behaviour
- admin CRUD screens
- validation rules
- API endpoints
- Twig-friendly internal service access

## 2. Objectives

### 2.1 Primary Objective
Reduce the time and repeated effort required to build custom PHP/MySQL/Twig websites that need reusable content modelling, admin editing, and structured API delivery.

### 2.2 Secondary Objectives
- improve consistency across future projects
- reduce hand-written CRUD duplication
- provide a deployable platform without a Node runtime requirement
- create a stable technical base for future expansion

## 3. Target Stack

- PHP 8.3+
- MariaDB 10.4+ or MySQL 8+
- Twig
- Bootstrap 5.3.3
- Vanilla JavaScript
- Composer
- Apache with mod_rewrite

## 4. User Roles

### 4.1 Super Admin
Full system control over:
- system settings
- users and roles
- schemas
- API keys
- content management
- menus and media

### 4.2 Content Admin
Can manage content types assigned to them, publish content, manage menus, and upload media.

### 4.3 Editor
Can create and edit draft content but may not publish or manage users.

### 4.4 API Consumer
Uses token-based access to retrieve or mutate content through the API based on permission scope.

### 4.5 Front-End Visitor
Consumes server-rendered Twig pages or front-end experiences powered by the internal service layer.

## 5. Core Features

### 5.1 Authentication
- login
- logout
- session handling
- secure password hashing
- CSRF protection for forms
- role-based route protection

### 5.2 Role-Based Access Control
- roles
- permissions
- permission groups
- route guards
- action-level authorisation in services/controllers

### 5.3 Schema-Driven Content Types
The system must allow content type definitions in configuration files stored in the repository.
Each schema must define:

- machine name
- display label
- single vs collection mode
- route slug settings
- draft/publish support
- allowed API actions
- field definitions
- relation definitions
- admin list behaviour

### 5.4 Generated Admin CRUD
From each content type schema, the system must generate:

- listing page
- create page
- edit page
- delete action
- validation feedback
- publish/unpublish actions where relevant
- filtering, sorting, and search where configured

### 5.5 Content Entry Management
Entries must support:

- title
- slug
- status
- created and updated timestamps
- publish timestamp
- author attribution
- JSON field payload
- optional revision history

### 5.6 Media Management
The system must support:

- file upload
- image upload
- metadata storage
- alt text/title/caption fields
- file usage linking
- safe storage path generation

### 5.7 Menus
Menu management must support:

- named menus
- nested items
- internal routes
- external URLs
- optional target attributes
- ordering

### 5.8 Settings
The system must support configurable grouped settings for:

- site metadata
- branding
- contact information
- SEO defaults
- feature toggles
- integration credentials stored securely where appropriate

### 5.9 API Layer
The system must expose REST endpoints for configured content types.
The API must support:

- listing
- single-item retrieval
- create/update/delete where permitted
- filtering
- sorting
- pagination
- consistent JSON response shapes
- token-based auth for protected actions

### 5.10 Twig Consumption
Twig templates must be able to use services or helpers to retrieve content internally without making HTTP requests to the app’s own API.

## 6. Functional Requirements

### FR-1 Bootstrap
The app must boot from `public/index.php`.

### FR-2 Routing
The app must support separate route files for:
- web
- admin
- API

### FR-3 Middleware
The app must support middleware chains for:
- sessions
- auth
- CSRF
- role checks
- API auth
- request logging

### FR-4 Config
The app must support environment-aware configuration and local override capability.

### FR-5 Content Type Loader
The app must load schema files from a canonical schema directory at boot or cached build time.

### FR-6 Validation
The app must derive validation rules from schema definitions and allow field-specific custom validators.

### FR-7 Serialisation
The app must serialise entries consistently for JSON responses.

### FR-8 Revisions
The app should store entry revisions whenever a configured content type is saved.

### FR-9 Publishing
Configured content types must support draft, published, and archived style states.

### FR-10 Search
Configured list screens and APIs must support searchable fields.

## 7. Non-Functional Requirements

### NFR-1 Deployability
The platform must be deployable on standard PHP hosting or VPS environments without a Node runtime.

### NFR-2 Maintainability
The codebase must be modular, PSR-aligned where practical, and clearly separated by responsibility.

### NFR-3 Performance
The platform must avoid expensive N+1 data access patterns in common list views.

### NFR-4 Security
The platform must implement:
- output escaping
- CSRF protection
- password hashing
- input validation
- permission checks
- upload restrictions
- rate limiting for API auth endpoints in later phases

### NFR-5 Testability
Core services, schema parsing, validation, and API response logic should be unit-testable and integration-testable.

## 8. Data Strategy

A hybrid content storage model will be used:

- fixed relational columns for standard entry metadata
- JSON payload for custom field values
- dedicated relation tables for relationships and media links

This avoids the complexity of fully dynamic EAV while keeping schemas flexible.

## 9. MVP Scope

The MVP must include:

- app bootstrap
- routing and middleware
- user auth
- roles and permissions
- schema-driven content types from files
- generated admin CRUD for content entries
- media upload basics
- settings management
- menus
- public JSON API for read operations
- protected JSON API for write operations
- Twig integration via internal services

## 10. Post-MVP Scope

After MVP, the following may be considered:

- GraphQL
- localisation
- schema builder UI
- plugin marketplace
- queues/jobs
- webhooks and outbound event subscriptions
- multisite

## 11. Acceptance Criteria

The release can be called V3 MVP-complete when all of the following are true:

1. a new content type can be created by adding one schema file
2. the admin panel can list, create, edit, delete, and publish entries for that type
3. the API can return the type in a paginated JSON list and single-item response
4. Twig templates can retrieve the same content through an internal service abstraction
5. users, roles, and permissions gate admin and API actions correctly
6. settings, menus, and media are managed through the admin UI
7. the project can be installed and run on a standard PHP stack using Composer

## 12. Release Boundary

The first release must favour reliability and reuse over breadth.
Any feature that meaningfully delays the stability of core content, admin, API, or deployment should be deferred.
