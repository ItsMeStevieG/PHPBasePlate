# PHPBasePlate V3 Database Schema

## 1. Database Strategy

PHPBasePlate V3 uses MariaDB/MySQL as the system of record.
The data model is intentionally hybrid:

- relational tables for stable system entities
- JSON payload storage for dynamic content field values
- supporting relation tables for structured links, media usage, and revisions

This keeps the system flexible while preserving queryability, integrity, and performance.

## 2. Conventions

- All primary keys use unsigned big integers.
- All core tables include `created_at` and `updated_at` where appropriate.
- Soft deletes are optional and should only be used where truly valuable.
- JSON columns should be used for dynamic payloads and structured settings.
- Foreign keys should be applied where safe and practical.

## 3. Core Tables

### 3.1 users
Stores user accounts.

**Columns**
- id
- name
- email
- password_hash
- status
- last_login_at nullable
- created_at
- updated_at

### 3.2 roles
Stores named roles.

**Columns**
- id
- name
- slug
- description nullable
- created_at
- updated_at

### 3.3 permissions
Stores permission definitions.

**Columns**
- id
- name
- slug
- group_name
- description nullable
- created_at
- updated_at

### 3.4 role_user
Pivot table between users and roles.

**Columns**
- user_id
- role_id

### 3.5 permission_role
Pivot table between roles and permissions.

**Columns**
- permission_id
- role_id

### 3.6 api_tokens
Stores API tokens.

**Columns**
- id
- name
- token_hash
- user_id nullable
- scopes_json
- last_used_at nullable
- expires_at nullable
- created_at
- updated_at

## 4. Content Modelling Tables

### 4.1 content_types
Registers available content types loaded from schema files.

**Columns**
- id
- machine_name unique
- label
- mode enum(`single`,`collection`)
- schema_path
- slug_field nullable
- title_field nullable
- status_field_enabled boolean
- revisioning_enabled boolean
- api_public_read boolean
- api_auth_write boolean
- config_json
- created_at
- updated_at

### 4.2 content_fields
Stores resolved field metadata for each content type.
This table acts as cached schema metadata and helps admin generation.

**Columns**
- id
- content_type_id
- machine_name
- label
- field_type
- sort_order
- is_required boolean
- is_searchable boolean
- is_filterable boolean
- is_sortable boolean
- config_json
- created_at
- updated_at

### 4.3 content_entries
Primary content storage table.

**Columns**
- id
- content_type_id
- title nullable
- slug nullable
- status enum(`draft`,`published`,`archived`)
- summary nullable
- payload_json
- created_by nullable
- updated_by nullable
- published_by nullable
- published_at nullable
- created_at
- updated_at

**Indexes**
- index on `content_type_id`
- index on `slug`
- index on `status`
- compound index on `content_type_id, status`
- compound unique where desired on `content_type_id, slug`

### 4.4 content_entry_revisions
Stores version history snapshots for entries.

**Columns**
- id
- content_entry_id
- revision_number
- title nullable
- slug nullable
- status
- summary nullable
- payload_json
- saved_by nullable
- created_at

### 4.5 content_relations
Generic relation table for linking entries to other entries.

**Columns**
- id
- source_entry_id
- field_name
- target_entry_id
- sort_order
- created_at

This supports one-to-many and ordered many-to-many style content links defined by relation fields.

## 5. Media Tables

### 5.1 media_files
Stores uploaded media metadata.

**Columns**
- id
- disk
- path
- file_name
- original_name
- extension
- mime_type
- size_bytes
- width nullable
- height nullable
- alt_text nullable
- title nullable
- caption nullable
- uploaded_by nullable
- meta_json nullable
- created_at
- updated_at

### 5.2 media_links
Links media to content entries.

**Columns**
- id
- media_file_id
- content_entry_id
- field_name
- sort_order
- created_at

This allows one entry to reference multiple media files in ordered sequences.

## 6. Settings Tables

### 6.1 settings_groups
Stores named settings groups.

**Columns**
- id
- machine_name unique
- label
- description nullable
- created_at
- updated_at

### 6.2 settings_items
Stores individual settings values.

**Columns**
- id
- settings_group_id
- machine_name
- label
- field_type
- value_json nullable
- is_secret boolean
- sort_order
- config_json nullable
- created_at
- updated_at

## 7. Menu Tables

### 7.1 menus
Stores menu containers.

**Columns**
- id
- machine_name unique
- label
- description nullable
- created_at
- updated_at

### 7.2 menu_items
Stores hierarchical menu items.

**Columns**
- id
- menu_id
- parent_id nullable
- label
- item_type enum(`internal`,`external`,`content`,`route`)
- url nullable
- route_name nullable
- content_entry_id nullable
- target nullable
- css_class nullable
- sort_order
- meta_json nullable
- created_at
- updated_at

## 8. Operational Tables

### 8.1 migrations
Tracks executed migrations.

**Columns**
- id
- migration
- batch

### 8.2 activity_log
Optional but strongly recommended.

**Columns**
- id
- actor_user_id nullable
- event_type
- entity_type
- entity_id nullable
- context_json nullable
- ip_address nullable
- user_agent nullable
- created_at

## 9. Suggested Example SQL Types

Use the following general data type guidance:

- IDs: `BIGINT UNSIGNED AUTO_INCREMENT`
- booleans: `TINYINT(1)`
- timestamps: `DATETIME`
- JSON payloads: `JSON`
- strings: `VARCHAR(191)` or larger where appropriate
- rich text: `LONGTEXT`

## 10. JSON Payload Design

`content_entries.payload_json` should contain custom field data in a stable machine-key format.

Example:

```json
{
  "hero_image": 14,
  "body": "<p>Example content</p>",
  "featured": true,
  "seo_title": "My Page",
  "tags": ["news", "community"]
}
```

Rules:
- keys match schema machine names
- relations that need ordering or richer metadata should use supporting tables
- do not put essential global entry metadata into payload_json if it deserves a first-class column

## 11. Why Not Full EAV

A full entity-attribute-value model would make ad hoc filtering, indexing, and query debugging more painful.
The hybrid model is preferred because:

- common metadata remains relational
- custom content stays flexible
- schema-driven admin generation still works
- future generated columns/indexes remain possible

## 12. Suggested Initial Foreign Keys

- role_user.user_id -> users.id
- role_user.role_id -> roles.id
- permission_role.permission_id -> permissions.id
- permission_role.role_id -> roles.id
- api_tokens.user_id -> users.id
- content_fields.content_type_id -> content_types.id
- content_entries.content_type_id -> content_types.id
- content_entries.created_by -> users.id
- content_entries.updated_by -> users.id
- content_entries.published_by -> users.id
- content_entry_revisions.content_entry_id -> content_entries.id
- media_links.media_file_id -> media_files.id
- media_links.content_entry_id -> content_entries.id
- settings_items.settings_group_id -> settings_groups.id
- menu_items.menu_id -> menus.id
- menu_items.parent_id -> menu_items.id

## 13. Initial Seed Data

The project should ship with seeders for:
- super admin role
- core permissions
- first admin user in installer flow or CLI setup
- default settings group `site`
- default menu `main`

## 14. Migration Policy

- All schema changes must be versioned through migrations.
- Migrations must be reversible where practical.
- Seeders must be idempotent where possible.
- Schema file changes that alter database assumptions must be reflected in migrations or regeneration steps.
