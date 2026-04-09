# PHPBasePlate V3 Claude Code Execution Guide

## 1. Purpose

This document is for agentic implementation in Claude Code.
It defines how work should be approached so the project is built in a controlled, dependency-aware way instead of drifting into ad hoc feature work.

## 2. Primary Rule

Always implement in the order defined in `07-implementation-plan.md`.
Do not skip ahead to admin UI, API polish, or front-end examples before the core substrate is stable.

## 3. Working Assumptions

The build stack is fixed unless explicitly changed by the project owner:

- PHP 8.3+
- MariaDB/MySQL
- Twig
- Bootstrap 5.3.3
- Vanilla JavaScript
- Composer
- Apache

No React, Next.js, Node.js runtime, Laravel, Symfony, or heavy external framework assumptions should be introduced into V3.

## 4. Agent Workflow

For each major milestone, Claude Code should follow this loop:

1. Read the relevant docs section.
2. Propose the exact files to create or update.
3. Implement the smallest coherent slice.
4. Verify syntax and logical consistency.
5. Summarise what changed.
6. Update or create docs if architecture changed.

## 5. Branch Discipline

- Work on branch: `v3`
- Use focused commits by concern
- Avoid mixing unrelated modules in the same change when possible

Example commit themes:
- `core: add request and response abstractions`
- `auth: implement login and session middleware`
- `content: add schema loader and field registry`
- `admin: add generated content list view`

## 6. Slice Size Guidance

Claude Code should prefer vertical but bounded slices.

Good slice examples:
- implement router + route registration + one example route
- implement auth middleware + login form + session store integration
- implement schema parser + validation + one example schema load test

Bad slice examples:
- build the whole CMS in one pass
- generate all field types before the field contract exists
- build every admin screen before content persistence is working

## 7. Definition of Done For Any Slice

A work slice is only done when:

- files are in the correct directories
- code matches the documented architecture
- no obvious syntax issues remain
- route/service/class names are consistent
- the slice is testable or manually verifiable
- docs are updated if the design changed materially

## 8. Required Early Deliverables

Claude Code should produce these in order:

### Stage A
- `composer.json` refresh if needed
- bootstrap files
- config files
- request/response abstractions
- router and dispatcher
- middleware contract and runner

### Stage B
- database connection layer
- migrations runner or migration mechanism
- logging and exception handling

### Stage C
- auth, sessions, users, roles, permissions

### Stage D
- schema files, schema loader, field registry, content repositories, entry service

### Stage E
- admin shell and generated CRUD

### Stage F
- API routes, serializers, query handling

### Stage G
- Twig integration, sample site pages, media, settings, menus, revisions

## 9. Coding Rules

### 9.1 Controllers Must Stay Thin
Controllers should:
- parse request input
- call services
- return responses

Controllers should not:
- contain complex SQL
- contain repeated business rules
- perform heavy rendering logic beyond selecting templates and view data

### 9.2 Twig Must Stay Presentational
Twig should:
- render data
- handle lightweight conditionals
- include partials/components

Twig should not:
- contain business logic
- issue HTTP requests to the local API
- build raw SQL or persistence logic

### 9.3 Services Own Business Logic
Services should:
- enforce workflow rules
- orchestrate repositories
- apply auth decisions and domain validation where needed

### 9.4 Repositories Own Persistence Logic
Repositories should:
- perform SQL/data access
- return consistent data structures or entity objects

## 10. Testing Priorities

As implementation progresses, tests should focus first on the most reusable pieces:

1. schema parsing
2. validation generation
3. slug generation
4. permission checks
5. API response shapes
6. content persistence

## 11. First Real Demonstration Goal

Before broad feature expansion, Claude Code should aim to prove the platform with one real content type.

Recommended first demonstration:

### `page`
Fields:
- title
- slug
- body
- seo_title
- seo_description
- hero_image
- published toggle

Demonstrate:
- admin create/edit
- public Twig rendering
- API read output

If this works cleanly, the architecture is on track.

## 12. Refactoring Policy

If Claude Code detects that a previous implementation step violated architecture, it should:

1. explain the issue clearly
2. propose a refactor
3. perform the refactor before adding dependent features

Do not paper over architectural drift with more code.

## 13. Docs-First Policy

If a design decision changes one of the following, update docs before or alongside the code:

- folder structure
- schema format
- API response format
- database model
- module boundaries
- authentication approach

## 14. Explicit Constraints

Claude Code must not:
- replace Twig with another templating engine
- add Node-based build assumptions as mandatory
- introduce framework lock-in contrary to the stated stack
- bypass the shared service layer by making internal HTTP calls to the same application
- collapse everything into a flat procedural structure

## 15. Final Build Standard

The end result should feel like:
- lightweight to deploy
- modern in architecture
- predictable in structure
- reusable across many websites
- comfortable for a PHP/Twig/MySQL developer to extend

That is the standard Claude Code should optimise for throughout implementation.
