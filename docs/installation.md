# PHPBasePlate V3 - Installation Guide

## Prerequisites

- **PHP 8.3+** with the following extensions:
  - pdo, pdo_mysql, mbstring, json, fileinfo, openssl, session
- **Composer 2+**
- **Apache** with `mod_rewrite` enabled (or Nginx with equivalent config)
- **MariaDB 10.4+** or **MySQL 8+** (optional - PHPBasePlate runs on JSON flat files by default)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/ItsMeStevieG/PHPBasePlate.git
cd PHPBasePlate
git checkout v3
```

### 2. Install dependencies

```bash
composer install
```

For production:

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure environment

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```ini
APP_ENV=local          # local, production
APP_DEBUG=true         # true for development, false for production
APP_URL=http://localhost:8000
APP_TIMEZONE=Australia/Sydney

# Storage: json (default, no DB needed), database, or auto (try DB, fall back to json)
STORAGE_DRIVER=json

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=phpbaseplate
DB_USERNAME=root
DB_PASSWORD=your_password

SESSION_DRIVER=file
LOG_CHANNEL=file
```

### 4. Create the database (only if using database driver)

Skip this step if using `STORAGE_DRIVER=json` (the default).

```sql
CREATE DATABASE phpbaseplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run environment check

```bash
php bin/check.php
```

This verifies PHP version, extensions, directory permissions, config files, and schemas. All 24 checks should pass.

### 6. Run setup

```bash
php bin/setup.php
```

This will:
1. Test the database connection
2. Run all 18 migrations (users, roles, permissions, content tables, media, settings, menus, activity log)
3. Run all 4 seeders (roles + permissions, admin user, default settings, default menus)
4. Sync content type schemas

When using `STORAGE_DRIVER=database`, setup also runs SQL migrations and database seeders.

JSON files are always seeded regardless of driver, so failover data is always available.

### 7. Start the development server

```bash
php -S localhost:8000 -t public/
```

### 8. Access the application

| URL | Description |
|---|---|
| http://localhost:8000 | Front-end site |
| http://localhost:8000/admin | Admin panel |
| http://localhost:8000/api/health | API health check |

**Default admin credentials:**

- Email: `admin@phpbaseplate.local`
- Password: `admin`

**Change the admin password after first login.**

**Note:** Login is rate-limited to 5 attempts per 15 minutes for brute force protection.

---

## Storage Drivers

PHPBasePlate supports two storage backends. Set `STORAGE_DRIVER` in `.env`:

### JSON Flat Files (default)

```ini
STORAGE_DRIVER=json
```

All data stored as JSON files in `storage/data/`. No database required. Ideal for development, small sites, or getting started quickly.

### MySQL / MariaDB

```ini
STORAGE_DRIVER=database
```

Full relational database storage. Requires a MySQL/MariaDB database and running `php bin/setup.php` to execute migrations.

When using the database driver, **every write also updates the JSON files automatically** (dual-write). This means:
- JSON files are always a live backup of the database
- You can switch to JSON mode instantly if the database goes down
- You can switch back to database mode at any time

### Auto-Failover

```ini
STORAGE_DRIVER=auto
```

Tries to connect to the database at boot. If the connection fails, automatically falls back to JSON flat files. A warning is logged. Useful for resilience.

### Switching Drivers

**JSON to Database:**
```bash
# 1. Set up MySQL and configure .env
STORAGE_DRIVER=database

# 2. Run setup (creates tables + seeds)
php bin/setup.php

# 3. Import existing JSON data into MySQL
php bin/sync.php json-to-db
```

**Database to JSON:**
```bash
# 1. Ensure JSON is current (should be automatic via dual-write)
php bin/sync.php status

# 2. Switch driver
STORAGE_DRIVER=json
```

### Sync Tool

```bash
php bin/sync.php status       # Compare record counts in both stores
php bin/sync.php db-to-json   # Full export: database -> JSON files
php bin/sync.php json-to-db   # Full import: JSON files -> database
```

---

## Apache Configuration

### Virtual Host (recommended)

```apache
<VirtualHost *:80>
    ServerName phpbaseplate.local
    DocumentRoot /var/www/phpbaseplate/public

    <Directory /var/www/phpbaseplate/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/phpbaseplate-error.log
    CustomLog ${APACHE_LOG_DIR}/phpbaseplate-access.log combined
</VirtualHost>
```

Enable the required modules:

```bash
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

### Shared Hosting

If you cannot set the document root to `public/`:

1. Upload the entire project to a directory **above** the web root
2. Point the domain's document root to the `public/` subdirectory
3. If that is not possible, the root `.htaccess` will redirect requests into `public/` automatically

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name phpbaseplate.local;
    root /var/www/phpbaseplate/public;
    index index.php;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;

    # Block access to hidden files
    location ~ /\. {
        deny all;
    }

    # Block PHP execution in uploads
    location ~* /uploads/.*\.php$ {
        deny all;
    }

    # Front controller
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~* \.(env|json|lock|md|xml|yml|yaml|log|sql)$ {
        deny all;
    }
}
```

---

## Directory Permissions

Ensure the web server user can write to these directories:

```bash
chmod -R 775 storage/
chmod -R 775 public/uploads/
chown -R www-data:www-data storage/ public/uploads/
```

---

## Production Checklist

Before going live:

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_URL` to your production domain
- [ ] Use strong database credentials
- [ ] Change the default admin password
- [ ] Uncomment HTTPS redirect in `public/.htaccess`
- [ ] Verify `storage/` and `public/uploads/` are writable
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `php bin/check.php` on the server
- [ ] Run `php bin/setup.php` to migrate and seed
- [ ] Verify admin login works
- [ ] Verify API health endpoint responds
- [ ] Verify front-end pages render correctly
- [ ] Set up log rotation for `storage/logs/`

---

## Troubleshooting

### "SQLSTATE[HY000] [2002] No such file or directory"
The database server is not running or the host is wrong. Check `DB_HOST` in `.env`. On some systems, use `127.0.0.1` instead of `localhost`.

### "SQLSTATE[HY000] [1049] Unknown database"
The database doesn't exist. Create it:
```sql
CREATE DATABASE phpbaseplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 500 errors with no detail
Set `APP_DEBUG=true` in `.env` to see the full error. Check `storage/logs/app.log`.

### Admin shows blank page
Clear the Twig cache:
```bash
rm -rf storage/cache/views/*
```

### Uploads not working
Check that `public/uploads/` is writable by the web server:
```bash
chmod -R 775 public/uploads/
```

### mod_rewrite not working
Enable it and restart Apache:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```
