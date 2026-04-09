# PHPBasePlate V3 Product Vision

## 1. Project Name

**PHPBasePlate V3**

## 2. Vision Statement

PHPBasePlate V3 will be a reusable, modern, API-first PHP platform for building websites and web applications using PHP, Twig, MySQL, Bootstrap, and vanilla JavaScript.

It must allow one codebase to support both:

- traditional server-rendered sites using Twig templates
- headless or semi-headless use cases through JSON API endpoints

The platform should feel lightweight enough for small and medium projects, while still being structured enough for long-term maintainability.

## 3. Why This Exists

Current options each solve part of the problem but not the whole problem for this stack:

- **October CMS** provides strong schema-driven backend ideas, but comes with Laravel-style deployment shape and greater framework weight.
- **Strapi** provides excellent headless CMS ergonomics, but is fundamentally a Node.js system with a React-based admin model.
- **Custom PHP websites** are easy to deploy, but usually require repeated reinvention of routing, auth, settings, CRUD admin, content modelling, and API output.

PHPBasePlate V3 exists to combine the best ideas of these systems into a platform that remains native to the preferred stack.

## 4. Positioning

PHPBasePlate V3 is:

- a **content platform kernel**
- a **schema-driven admin and API engine**
- a **Twig-capable website foundation**
- a **reusable starter for future client projects**

PHPBasePlate V3 is not:

- a Laravel clone
- a Node replacement runtime
- a React admin platform
- a fully static site generator

## 5. Guiding Principles

### 5.1 Stack Fidelity
The system must remain faithful to the chosen stack:

- PHP handles application execution
- Twig handles server-rendered templates
- MySQL/MariaDB handles relational persistence
- Bootstrap handles admin and default front-end UI
- JavaScript is used only where interaction is needed

### 5.2 Deployment Simplicity
The system must be deployable on:

- standard Apache PHP hosting where practical
- VPS environments with normal Composer-based deployment
- local XAMPP-style development environments

No permanent Node.js runtime should be required in production.

### 5.3 Reuse First
Each major project concern must be abstracted so the system can be reused across projects:

- content types
n- fields
- menus
- settings
- users and roles
- APIs
- themes/templates

### 5.4 One Service Layer, Multiple Outputs
Business logic must not be duplicated between Twig pages and API endpoints.

The same service layer must be capable of:

- serving content to Twig templates internally
- serving JSON to external consumers
- serving data to the admin panel

### 5.5 Schema-Driven Behaviour
Content structure should be declared through machine-readable schema files.

From a single schema definition, the system should derive:

- validation rules
- admin forms
- list views
- storage mapping
- API serialisation
- search and filter metadata

### 5.6 Practical Over Clever
Architectural choices must favour clarity and maintainability over novelty.

The system should avoid:

- unnecessary magic
- hidden conventions without documentation
- deep runtime metaprogramming
- excessive package sprawl

## 6. Primary User Personas

### 6.1 Solo Developer
A single developer building many sites and wanting a reusable base with auth, content, API, admin, and front-end patterns already solved.

### 6.2 Small Agency Developer
A developer or small team building bespoke websites for clients who need custom fields, admin editing, and easy deployment.

### 6.3 Hybrid Website Builder
A developer building a front-end Twig site today but wanting the same content model to also power JSON endpoints, mobile apps, or future front-end clients.

## 7. Product Goals

1. Make it fast to scaffold a new project.
2. Make content types configurable without hand-coding CRUD repeatedly.
3. Make the admin interface predictable and easy to extend.
4. Make it easy to expose data through a stable API.
5. Make Twig integration first-class rather than an afterthought.
6. Make deployment simpler than heavier framework-CMS stacks.
7. Make future evolution possible through modules and hooks.

## 8. Non-Goals for V3

The first serious version should explicitly avoid the following scope creep:

- GraphQL in the initial release
- multi-tenant SaaS architecture
- real-time collaboration
- drag-and-drop page builder
- visual schema builder inside the browser
- multi-language localisation framework
- distributed queue workers
- plugin marketplace

These may become future milestones, but they are not required for the first stable version.

## 9. Product Shape

The product should contain the following major areas:

### 9.1 Core Application Kernel
- bootstrap
- config loading
- service container or resolver
- router
- middleware pipeline
- event system
- logging and error handling

### 9.2 Authentication and Authorisation
- user login/logout
- password hashing and resets later
- roles
- permissions
- admin route protection
- API token support

### 9.3 Content Engine
- content type definitions
- field definitions
- entries
- slugs
- status and publishing
- revisions
- relationships

### 9.4 Admin UI
- Bootstrap/Twig admin layout
- generated CRUD screens
- file/media manager
- settings screens
- menu editor
- user management

### 9.5 Delivery Layer
- Twig rendering
- internal service-based content access
- JSON REST endpoints
- serialisers and transformers

## 10. Success Criteria

PHPBasePlate V3 should be considered strategically successful when it can:

1. create a content type from a schema file
2. auto-generate a working admin CRUD interface
3. store entries in MySQL with sane relational structure
4. expose entries through a documented REST API
5. render the same content through Twig without HTTP self-calls
6. manage media, settings, and menus
7. deploy to a normal PHP environment without requiring a Node runtime

## 11. Strategic Decision

PHPBasePlate V3 should be treated as the new future of the repository.
The previous implementation may be superseded entirely once the new architecture is stable.
