# PHPBasePlate V3 System Architecture

## 1. Architectural Style

PHPBasePlate V3 will use a modular monolith architecture.

This means:

- one deployable PHP application
- clear module boundaries internally
- a shared service layer consumed by web, admin, and API controllers
- no unnecessary microservice decomposition

This is the correct fit for the intended scale, hosting model, and maintainability goals.

## 2. High-Level Runtime Model

```text
HTTP Request
  -> public/index.php
  -> bootstrap/app.php
  -> router
  -> middleware pipeline
  -> controller/action
  -> domain service
  -> repository/data access
  -> response factory
  -> Twig HTML or JSON response
```

## 3. Application Layers

### 3.1 Bootstrap Layer
Responsible for:
- environment loading
- config loading
- autoload bootstrapping
- service container/resolver bootstrapping
- route registration
- middleware registration
- exception handling

### 3.2 HTTP Layer
Responsible for:
- request abstraction
- response abstraction
- route definitions
- route dispatch
- middleware execution
- controller entry points

### 3.3 Domain Layer
Responsible for:
- content type loading
- content entry lifecycle
- auth decisions
- permission enforcement
- menu and settings logic
- media lifecycle rules

### 3.4 Persistence Layer
Responsible for:
- query execution
- repositories
- transactions
- mapping between relational data and domain objects/arrays
- migration support

### 3.5 Presentation Layer
Responsible for:
- Twig rendering
- admin templates
- API serialisation
- form rendering helpers
- Bootstrap-compatible UI components

## 4. Core Modules

### 4.1 Core
Contains:
- App kernel
- Config manager
- Env loader
- Service container
- Router
- Middleware dispatcher
- Logger
- Exception handler
- View renderer integration

### 4.2 Auth
Contains:
- User authentication
- Session management
- Password verification
- Role and permission checks
- Access gates/policies
- API token validation

### 4.3 Content
Contains:
- Schema loader
- Field registry
- Content type manager
- Entry service
- Validation service
- Revision service
- Publish workflow
- Relationship service

### 4.4 Admin
Contains:
- CRUD controllers
- table/list builders
- form builders
- admin navigation
- dashboard widgets later

### 4.5 API
Contains:
- REST controllers
- filters and query parsers
- serialisers/transformers
- API auth middleware
- response formatting

### 4.6 Frontend
Contains:
- web routes
- controllers for site pages
- Twig helpers/functions
- view models or presenters where needed

### 4.7 Media
Contains:
- upload validation
- storage path generation
- metadata extraction where applicable
- file serving integration strategy
- usage linking

### 4.8 Settings and Menus
Contains:
- grouped settings service
- menu tree service
- admin management screens

## 5. Request Lifecycles

### 5.1 Admin Page Request
1. Request enters `public/index.php`
2. Bootstrap loads config and services
3. Router matches `/admin/...`
4. Middleware enforces session auth and role checks
5. Controller requests data from domain services
6. Twig renders Bootstrap-based admin template
7. Response is sent

### 5.2 API Request
1. Request enters front controller
2. API route is matched
3. API middleware validates token or public access rule
4. Query parameters are parsed into filters/sort/pagination
5. Content service fetches data
6. Serialiser normalises output
7. JSON response is sent

### 5.3 Public Twig Page Request
1. Web route is matched
2. Controller resolves content through internal services
3. No self-HTTP API call is made
4. Twig template renders content
5. Response is sent

## 6. Service Layer Rules

The service layer is the centre of the application and must be treated as the primary business logic boundary.

### Rules
- Controllers must stay thin.
- Controllers should orchestrate request/response handling only.
- Data access should not be embedded directly inside templates.
- Services may call repositories.
- Twig helpers may call services, but should not directly perform low-level SQL.

## 7. Schema Loading Strategy

Schemas should live in a dedicated directory such as `resources/schemas`.

At runtime, the schema loader should:
- discover schema files
- parse and validate structure
- register content types
- register field definitions
- optionally cache processed schema metadata to disk for performance

Schema validation failures should fail loudly in development.

## 8. Content Storage Strategy

A hybrid storage model will be used.

### Base entry table stores:
- id
- content_type_id
- title
- slug
- status
- summary optional if desired
- payload JSON
- author ids
- timestamps
- publish timestamp

### Supporting tables store:
- relations
- revisions
- media links
- taxonomy later if needed

This provides flexibility without the performance and complexity penalty of full EAV.

## 9. Field System Architecture

Each field type should implement a common contract covering:
- schema validation
- form rendering config
- server-side validation rules
- normalisation before save
- serialisation for API output
- display formatting for admin list/table output

### Initial field types
- text
- textarea
- richtext
- slug
- number
- boolean
- date
- datetime
- select
- image
- file
- relation
- repeater
- json

## 10. Security Architecture

### 10.1 Sessions
- secure session start and regeneration
- session-based admin auth

### 10.2 Passwords
- PHP password hashing functions only
- no custom crypto for passwords

### 10.3 CSRF
- all state-changing form requests require CSRF verification

### 10.4 Validation
- all user input validated server-side
- schema rules converted into validator rules

### 10.5 Authorisation
- every admin action and protected API action checked against role/permission rules

### 10.6 Upload Safety
- whitelist allowed MIME types/extensions
- generate sanitised file names
- prevent executable upload exposure

## 11. Caching Strategy

V3 should support simple file-based caching for:
- compiled Twig views if configured
- route cache later
- schema cache
- settings cache

Complex distributed caching is out of scope for MVP.

## 12. Extension Model

V3 should expose a lightweight hooks/events system.

Initial extension points may include:
- before content save
- after content save
- before content delete
- after content publish
- before API response serialisation
- admin navigation registration

The event system should remain explicit and documented.

## 13. Error Handling

The system must provide:
- central exception handling
- development-friendly error details when debug is enabled
- production-safe generic error output when debug is disabled
- structured logging to files in `storage/logs`

## 14. Architectural Boundaries

The following boundaries must be respected:

- Twig templates must not contain business logic beyond light presentation conditionals.
- Controllers must not become query builders.
- Repositories must not render views.
- Services must not depend on Twig.
- API serialisers must not leak internal DB implementation details.

## 15. Future-Proofing Decisions

The following design decisions are intentional to support future growth:

- modular monolith instead of ad hoc flat PHP files
- schema-driven content rather than hard-coded tables per project
- shared service layer for both Twig and API
- hybrid relational + JSON content storage
- event hooks for future plugin-like extensions

This should keep V3 practical today while giving enough structure for long-term reuse.
