#!/usr/bin/env python3
"""Generate complete Laravel PM API project."""
import os

BASE = '/root/projects/pm-api-laravel'

def w(path, content):
    full = os.path.join(BASE, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, 'w') as f:
        f.write(content.strip() + '\n')
    print(f"  [created] {path}")

print("Generating PM API Laravel project...")

# === 1. composer.json ===
w('composer.json', """{
    "name": "laravel/laravel",
    "type": "project",
    "description": "Project Management API System",
    "keywords": ["laravel", "api", "project-management"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "ext-pdo": "*",
        "ext-pgsql": "*",
        "barryvdh/laravel-dompdf": "^3.0",
        "maatwebsite/excel": "^3.1"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "app/",
            "Database\\\\Factories\\\\": "database/factories/",
            "Database\\\\Seeders\\\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\\\Foundation\\\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "laravel/pint": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}""")

# === 2. .gitignore ===
w('.gitignore', """> .env
> .env.backup
> .env.production
> .phpunit.result.cache
> node_modules/
> vendor/
> /public/storage
> /.idea
> /.vscode
> *.log
> *.sqlite
> /database/database.sqlite
> /.hermes
> .DS_Store
> Thumbs.db""")

# === 3. .dockerignore ===
w('.dockerignore', ".env\n.env.*\n*.log\nnode_modules\nvendor\n.git\n.idea\n.vscode\n.DS_Store\nThumbs.db\n.env.local\nstorage/*\nbootstrap/cache/*")

# === 4. Dockerfile ===
w('Dockerfile', """FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    apache2 apache2-dev \
    postgresql-dev \
    git curl unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && a2enmod rewrite \
    && apk del --purge postgresql-dev apache2-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs \
    && chmod -R 777 storage bootstrap/cache

COPY .docker/apache.conf /etc/apache2/conf-enabled/000-laravel.conf

EXPOSE 80

CMD ["apache2-foreground"]""")

# === 5. .docker/apache.conf ===
w('.docker/apache.conf', """<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>

DocumentRoot /var/www/html/public

<Directory /var/www/html/storage>
    Require all granted
</Directory>

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined""")

# === 6. docker-compose.yml ===
w('docker-compose.yml', """services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: pm-api-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
    ports:
      - "8080:80"
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - pm-network
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_PORT=5432
      - DB_DATABASE=pm_api
      - DB_USERNAME=pm_api_user
      - DB_PASSWORD=pm_api_secret
      - REDIS_HOST=redis
      - REDIS_PORT=6379

  db:
    image: postgres:16-alpine
    container_name: pm-api-db
    restart: unless-stopped
    environment:
      POSTGRES_DB: pm_api
      POSTGRES_USER: pm_api_user
      POSTGRES_PASSWORD: pm_api_secret
    volumes:
      - pgdata:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U pm_api_user -d pm_api"]
      interval: 5s
      timeout: 5s
      retries: 5
    networks:
      - pm-network

  redis:
    image: redis:7-alpine
    container_name: pm-api-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 5s
      retries: 5
    networks:
      - pm-network

networks:
  pm-network:
    driver: bridge

volumes:
  pgdata:""")

# === 7. .env.example ===
w('.env.example', """APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=pm_api
DB_USERNAME=pm_api_user
DB_PASSWORD=pm_api_secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SANCTUM_STATEFUL_DOMAINS=localhost:8080,127.0.0.1:8080
SESSION_DOMAIN=localhost""")

# === 8. Makefile ===
w('Makefile', """APP_DIR=/root/projects/pm-api-laravel

setup:
\tcd $(APP_DIR) && composer install && cp .env.example .env && php artisan key:generate
\tdocker-compose up -d db redis
\tdocker-compose exec -T app php artisan migrate --force
\tdocker-compose exec -T app php artisan db:seed

up:
\tcd $(APP_DIR) && docker-compose up -d

down:
\tcd $(APP_DIR) && docker-compose down

logs:
\tcd $(APP_DIR) && docker-compose logs -f app

migrate:
\tcd $(APP_DIR) && docker-compose exec -T app php artisan migrate --force

migrate-fresh:
\tcd $(APP_DIR) && docker-compose exec -T app php artisan migrate:fresh --seed

seed:
\tcd $(APP_DIR) && docker-compose exec -T app php artisan db:seed

bash-app:
\tcd $(APP_DIR) && docker-compose exec app sh

bash-db:
\tcd $(APP_DIR) && docker-compose exec db psql -U pm_api_user -d pm_api""")

# === 9. phpunit.xml ===
w('phpunit.xml', """<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>""")

# === 10. .editorconfig ===
w('.editorconfig', """# EditorConfig helps maintain consistent coding styles
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true
indent_style = space
indent_size = 4

[*.php]
indent_size = 4

[*.md]
trim_trailing_whitespace = false

[*.yml]
indent_size = 2

[*.json]
indent_size = 2""")

# === 11. env.php (for Docker entrypoint) ===
w('env.php', """<?php
$env = $_ENV['APP_ENV'] ?? 'production';
$dotenvPath = __DIR__ . '/.env';

if (file_exists($dotenvPath)) {
    // Simple .env parser (for Docker environments)
    // Laravel will handle this via bootstrap
}

// Generate APP_KEY if not set
$appKey = ($_ENV['APP_KEY'] ?? null);
if (empty($appKey) && $env === 'production') {
    // Generate a new key
    $key = 'base64:' . base64_encode(random_bytes(32));
    $envContent = file_get_contents($dotenvPath);
    $envContent = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $envContent);
    file_put_contents($dotenvPath, $envContent);
}""")

print("[OK] Batch 1 complete: Foundation files")
