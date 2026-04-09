# PHPBasePlate V3 Deployment and Environments

## 1. Deployment Philosophy

PHPBasePlate V3 should be practical to deploy on both:

- a VPS with SSH and Composer access
- standard PHP hosting where document root and PHP version can be controlled

The application is a PHP system, not a static export and not a Node runtime app.

## 2. Environment Targets

### 2.1 Local Development
Recommended local targets:
- XAMPP
- local Apache/PHP/MySQL stack
- Docker later if desired, but not required for the project design

### 2.2 Shared Hosting
Supported where the host allows:
- PHP 8.3+
- correct extensions
- document root pointing to `public/` or an equivalent workaround
- writable storage paths

### 2.3 VPS
Preferred production target for maximum control.

## 3. Required PHP Extensions

The exact list may evolve, but the initial implementation should assume:
- PDO
- pdo_mysql
- mbstring
- json
- fileinfo
- openssl
- session

If image manipulation is later added, document any additional extension requirements.

## 4. Document Root

The web server must point to:

```text
/public
```

Sensitive application code must not be publicly accessible.

## 5. Writable Directories

Ensure these paths are writable in production:

- `storage/cache`
- `storage/logs`
- `storage/sessions`
- `public/uploads` if direct public upload storage is used

## 6. Environment Variables

Suggested `.env` keys:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_TIMEZONE=Australia/Sydney
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=phpbaseplate
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=file
LOG_CHANNEL=file
```

Additional keys may be added for mail, integrations, and storage options later.

## 7. Shared Hosting Strategy

If using shared hosting:

1. Upload the full project outside or above the public web root where possible.
2. Point the domain/subdomain document root to the `public/` directory.
3. If that is not possible, mirror only the intended public assets into the web root and keep app code outside public exposure.
4. Upload `vendor/` if Composer cannot be run on the host.
5. Ensure PHP version and extensions match local development assumptions.
6. Set writable permissions only on the required runtime directories.

## 8. VPS Strategy

Preferred deployment flow:

1. clone repository to server
2. checkout `v3` or the active release branch
3. run `composer install --no-dev --optimize-autoloader`
4. configure `.env`
5. run migrations and seeders
6. verify writable paths
7. point Apache/Nginx to `public/`
8. verify health checks and admin login

## 9. Release Checklist

Before any production release:

- [ ] `.env.example` is current
- [ ] migrations are committed
- [ ] seeders are correct
- [ ] admin login works
- [ ] API public read endpoints work
- [ ] API protected writes require valid token/permission
- [ ] Twig site pages render correctly
- [ ] storage paths are writable
- [ ] logs are being written
- [ ] debug mode is off in production

## 10. Production Hardening

Recommended production defaults:
- `APP_DEBUG=false`
- secure cookies where HTTPS is available
- session regeneration after login
- restricted file upload types
- no directory listing in public assets
- error logging enabled
- stack traces hidden from users

## 11. Build/Deploy Separation

PHPBasePlate V3 does not need a front-end asset build pipeline to function.
If optional asset pipelines are later introduced, they must remain optional and must not become a mandatory production runtime dependency.

## 12. Upgrade Strategy

For V3 evolution:
- version all DB changes through migrations
- keep config defaults backward-aware where practical
- document breaking changes in release notes
- avoid silent schema contract changes

## 13. Operational Diagnostics

The platform should eventually expose or support:
- environment validation checks
- writable path checks
- PHP extension checks
- database connectivity checks
- schema loading checks

These may exist as a CLI command, admin diagnostics screen, or both.

## 14. Recommended Hosting Posture

For serious production use, a VPS is still the preferred option because it provides:
- predictable Composer usage
- proper document root control
- easier debugging
- cleaner permissions management
- easier backups and deployment automation

Shared hosting may still be suitable for smaller projects if the required constraints are met.
