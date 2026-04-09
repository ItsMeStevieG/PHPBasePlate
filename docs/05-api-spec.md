# PHPBasePlate V3 REST API Specification

## 1. API Philosophy

The API is a first-class delivery mechanism for content, but it is not the only consumer path.
The same underlying services must power both API output and Twig rendering.

The API should be:
- predictable
- documented
- token-aware
- content-type driven
- easy to consume from JavaScript or external systems

## 2. Base URL

Recommended pattern:

```text
/api
```

Examples:
- `/api/news`
- `/api/news/my-first-post`
- `/api/settings/site`

## 3. Response Format

All responses should follow a consistent envelope.

### 3.1 Success Response

```json
{
  "success": true,
  "message": "Entries retrieved successfully.",
  "data": [],
  "meta": {
    "pagination": {
      "page": 1,
      "per_page": 20,
      "total": 120,
      "total_pages": 6
    }
  },
  "errors": []
}
```

### 3.2 Error Response

```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "meta": {},
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## 4. Authentication

### 4.1 Public Read Endpoints
Public content types may allow unauthenticated `GET` access.

### 4.2 Protected Endpoints
Write operations and protected reads require token auth.

Recommended header:

```text
Authorization: Bearer {token}
```

### 4.3 Token Storage
Only token hashes should be stored in the database.

## 5. Content Endpoints

For a content type such as `news`, the API should expose:

### 5.1 List Entries
`GET /api/news`

### Query Parameters
- `page`
- `per_page`
- `sort`
- `direction`
- `status`
- `search`
- field-specific filters where allowed by schema

### 5.2 Get Single Entry by Identifier
`GET /api/news/{identifier}`

The identifier may be:
- numeric id
- slug

Behaviour should be configurable per content type.

### 5.3 Create Entry
`POST /api/news`

### 5.4 Update Entry
`PUT /api/news/{id}`
`PATCH /api/news/{id}`

### 5.5 Delete Entry
`DELETE /api/news/{id}`

### 5.6 Publish Entry
`POST /api/news/{id}/publish`

### 5.7 Unpublish Entry
`POST /api/news/{id}/unpublish`

## 6. Settings Endpoints

### 6.1 Get Group Settings
`GET /api/settings/{group}`

### 6.2 Update Group Settings
`PUT /api/settings/{group}`

Protected by admin or settings-specific permissions.

## 7. Menu Endpoints

### 7.1 List Menus
`GET /api/menus`

### 7.2 Get Single Menu
`GET /api/menus/{machine_name}`

### 7.3 Update Menu Structure
`PUT /api/menus/{machine_name}`

## 8. Media Endpoints

### 8.1 Upload Media
`POST /api/media`

### 8.2 Get Media Item
`GET /api/media/{id}`

### 8.3 Delete Media Item
`DELETE /api/media/{id}`

## 9. User/Admin Endpoints

These should remain protected and internal-facing for the admin UI where practical.

Examples:
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/me`

## 10. Query Rules

### 10.1 Pagination Defaults
- default `per_page`: 20
- maximum `per_page`: 100

### 10.2 Sorting
Allowed only on schema-approved fields.

Example:

```text
GET /api/news?sort=published_at&direction=desc
```

### 10.3 Search
Search should apply only to schema-marked searchable fields.

Example:

```text
GET /api/news?search=emmaville
```

### 10.4 Filters
Schema-marked filterable fields should support simple equality filters initially.

Examples:

```text
GET /api/news?status=published
GET /api/events?featured=1
```

## 11. Serialisation Rules

Every content entry should serialise into a stable shape.

Example:

```json
{
  "id": 22,
  "type": "news",
  "title": "Project update",
  "slug": "project-update",
  "status": "published",
  "summary": "Short summary",
  "published_at": "2026-04-10 09:00:00",
  "created_at": "2026-04-08 16:00:00",
  "updated_at": "2026-04-09 12:00:00",
  "fields": {
    "body": "<p>Full body</p>",
    "featured": true,
    "hero_image": 14
  },
  "links": {
    "self": "/api/news/project-update"
  }
}
```

Rules:
- system fields remain top-level
- dynamic fields remain inside `fields`
- relations may be embedded or linked later
- do not leak raw internal config values unnecessarily

## 12. Validation Errors

Validation errors must return:
- HTTP 422
- field-specific messages
- stable error structure

## 13. HTTP Status Codes

Use these consistently:
- 200 OK
- 201 Created
- 204 No Content
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 409 Conflict where applicable
- 422 Unprocessable Entity
- 500 Internal Server Error

## 14. Security Rules

- Public endpoints must respect content type visibility.
- Draft content must not leak through public read endpoints unless explicitly authorised.
- Write endpoints must enforce both token validity and permission scope.
- Upload endpoints must validate MIME and file size.

## 15. Example Schema-to-API Mapping

Given schema `news.json`, the system should auto-register API capabilities according to config:

```json
{
  "name": "news",
  "api": {
    "public_read": true,
    "auth_write": true,
    "allow_delete": true
  }
}
```

This should translate to route registration and controller capability checks automatically.

## 16. API Versioning

V3 MVP may begin without a visible `/v1` prefix if preferred, but the internal design should be capable of adding version namespaces later.

Recommended future-ready pattern:

- current: `/api/news`
- future: `/api/v1/news`

## 17. Internal Use Rule

Twig templates should not call these API endpoints over HTTP internally.
They should call domain services directly.
The API exists for external consumption, JavaScript clients, and admin AJAX where appropriate.
