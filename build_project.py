#!/usr/bin/env python3
"""
Comprehensive Laravel PM API project generator.
Creates ALL files for a complete Laravel 11+ Project Management API system.
Target: /root/projects/pm-api-laravel
"""
import os, json

BASE = "/root/projects/pm-api-laravel"
os.makedirs(BASE, exist_ok=True)

created = []

def w(path, content):
    """Write a file, return progress."""
    full = os.path.join(BASE, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "w") as f:
        f.write(content.strip() + "\n")
    created.append(path)

def section(title):
    print(f"\n{'='*60}")
    print(f"  {title}")
    print(f"{'='*60}")

###############################################################################
# 1. PROJECT INFRASTRUCTURE
###############################################################################
section("1. Project Infrastructure (composer, docker, etc.)")

w('composer.json', json.dumps({
    "name": "laravel/laravel",
    "type": "project",
    "description": "Project Management API System",
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "ext-pdo": "*",
        "ext-pgsql": "*",
        "barryvdh/laravel-dompdf": "^3.0",
        "maatwebsite/excel": "^3.1"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "phpunit/phpunit": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {"psr-4": {"Tests\\": "tests/"}},
    "scripts": {
        "post-autoload-dump": ["Illuminate\\Foundation\\ComposerScripts::postAutoloadDump"],
        "post-root-package-install": ["@php -r \"file_exists('.env') || copy('.env.example', '.env');\""],
        "post-create-project-cmd": ["@php artisan key:generate --ansi"]
    },
    "config": {"optimize-autoloader": True, "preferred-install": "dist", "sort-packages": True},
    "minimum-stability": "stable",
    "prefer-stable": True
}, indent=2)

w('.env.example', '''APP_NAME="PM API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=pm_api
DB_USERNAME=pm_api_user
DB_PASSWORD=pm_api_secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

SESSION_DRIVER=file
SESSION_DOMAIN=localhost''')

w('Makefile', 'APP_DIR=/root/projects/pm-api-laravel\n\nsetup:\n\tcd $(APP_DIR) && composer install && cp .env.example .env && php artisan key:generate\nup:\n\tcd $(APP_DIR) && docker compose up -d\ndown:\n\tcd $(APP_DIR) && docker compose down -v\nlogs:\n\tcd $(APP_DIR) && docker compose logs -f app\nmigrate:\n\tcd $(APP_DIR) && docker compose exec -T app php artisan migrate --seed\nbash:\n\tcd $(APP_DIR) && docker compose exec app sh\n')

# ============================================================
# 2. DOCKER
# ============================================================
section("2. Docker Files")

w('Dockerfile', '''FROM php:8.3-fpm-alpine

RUN apk add --no-cache apache2 apache2-dev postgresql-dev git curl unzip && \
    docker-php-ext-install pdo pdo_pgsql pgsql && \
    a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs && \
    chmod -R 777 storage bootstrap/cache

COPY .docker/apache.conf /etc/apache2/conf-enabled/000-laravel.conf

EXPOSE 80
CMD ["apache2-foreground"]
''')

w('.docker/apache.conf', '''<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>
DocumentRoot /var/www/html/public
ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined
''')

w('docker-compose.yml', '''services:
  app:
    build: .
    container_name: pm-api-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes: [".:/var/www/html"]
    ports: ["8080:80"]
    depends_on: { db: {condition: service_healthy}, redis: {condition: service_healthy} }
    networks: [pm-network]
    env_file: .env

  db:
    image: postgres:16-alpine
    container_name: pm-api-db
    environment: { POSTGRES_DB: pm_api, POSTGRES_USER: pm_api_user, POSTGRES_PASSWORD: pm_api_secret }
    volumes: [pgdata:/var/lib/postgresql/data]
    ports: ["5432:5432"]
    healthcheck: { test: ["CMD-SHELL", "pg_isready -U pm_api_user -d pm_api"], interval: 5s, timeout: 5s, retries: 5 }
    networks: [pm-network]

  redis:
    image: redis:7-alpine
    container_name: pm-api-redis
    ports: ["6379:6379"]
    healthcheck: { test: ["CMD", "redis-cli", "ping"], interval: 5s, timeout: 5s, retries: 5 }
    networks: [pm-network]

networks: { pm-network: {driver: bridge} }
volumes: { pgdata: }
''')

section("3. Laravel Core")

# artisan
w('artisan', '#!/usr/bin/env php\n<?php\ndefine(\'LARAVEL_START\', microtime(true));\nrequire __DIR__."/vendor/autoload.php";\n$app = require_once __DIR__."/bootstrap/app.php";\n$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n$status = $kernel->handle($input = new Symfony\\Component\\Console\\Input\\ArgvInput, new Symfony\\Component\\Console\\Output\\ConsoleOutput);\n$kernel->terminate($input, $status);\nexit($status);\n')
os.chmod(os.path.join(BASE, 'artisan'), 0o755)

# bootstrap/app.php
w('bootstrap/app.php', '''<?php

use Illuminate\\Foundation\\Application;
use Illuminate\\Foundation\\Configuration\\Exceptions;
use Illuminate\\Foundation\\Configuration\\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api([
            \\Laravel\\Sanctum\\Http\\Middleware\\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \\Illuminate\\Routing\\Middleware\\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();
''')

w('bootstrap/providers.php', '''<?php
return [
    App\\Providers\\AppServiceProvider::class,
];
''')

# config/ files
w('config/app.php', '''<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'UTC',
    'locale' => 'en',
    'keys' => [
        'app' => 'APP_KEY',
        'cipher' => 'AES-256-CBC',
    ],
    'providers' => [
        Illuminate\\Foundation\\Providers\\FoundationServiceProvider::class,
        Illuminate\\Routing\\RoutingServiceProvider::class,
        Barryvdh\\DomPDF\\ServiceProvider::class,
        App\\Providers\\AppServiceProvider::class,
    ],
    'aliases' => [
        'Excel' => Maatwebsite\\Excel\\Facades\\Excel::class,
    ],
];
''')

w('config/database.php', '''<?php
return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '', 'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver' => 'mysql', 'url' => env('DB_URL'), 'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'), 'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'), 'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4', 'prefix' => '', 'prefix_indexes' => true, 'strict' => true,
        ],
        'pgsql' => [
            'driver' => 'pgsql', 'url' => env('DB_URL'), 'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'), 'database' => env('DB_DATABASE', 'pm_api'),
            'username' => env('DB_USERNAME', 'pm_api_user'), 'password' => env('DB_PASSWORD', 'pm_api_secret'),
            'charset' => 'utf8', 'prefix' => '', 'prefix_indexes' => true,
            'search_path' => 'public', 'sslmode' => 'prefer',
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv', 'url' => env('DB_URL'), 'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '1433'), 'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'), 'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8', 'prefix' => '', 'prefix_indexes' => true,
        ],
    ],
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => ['cluster' => env('REDIS_CLUSTER', 'redis'), 'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME','laravel'),'_').'_database_')],
        'default' => ['url' => env('REDIS_URL'), 'host' => env('REDIS_HOST','127.0.0.1'), 'password' => env('REDIS_PASSWORD'), 'port' => env('REDIS_PORT','6379'), 'database' => env('REDIS_DB','0')],
        'cache' => ['url' => env('REDIS_URL'), 'host' => env('REDIS_HOST','127.0.0.1'), 'password' => env('REDIS_PASSWORD'), 'port' => env('REDIS_PORT','6379'), 'database' => env('REDIS_CACHE_DB','1')],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
];
''')

w('config/auth.php', '''<?php
return [
    'defaults' => ['guard' => 'api', 'passwords' => 'users'],
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'api' => ['driver' => 'sanctum', 'provider' => 'users'],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => env('AUTH_MODEL', App\\Models\\User::class)],
    ],
    'passwords' => ['users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60]],
    'password_timeout' => 10800,
];
''')

w('config/sanctum.php', '''<?php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
    'guard' => ['api'],
    'expiration' => null,
    'middleware' => [
        'authenticate_session' => Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession::class,
    ],
];
''')

w('config/cors.php', '''<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
''')

w('config/services.php', '''<?php
return [
    'postgresql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST'), 'port' => env('DB_PORT'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'), 'password' => env('DB_PASSWORD'),
    ],
];
''')

w('config/filesystems.php', '''<?php
return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => ['driver' => 'local', 'root' => storage_path('app'), 'throw' => false],
        'public' => ['driver' => 'local', 'root' => storage_path('app/public'), 'url' => env('APP_URL').'/storage', 'visibility' => 'public', 'throw' => false],
    ],
    'links' => [public_path('storage') => storage_path('app/public')],
];
''')

# ============================================================
# 4. MODELS
# ============================================================
section("4. Models (with all relationships)")

# --- User (scaffold model for Sanctum) ---
w('app/Models/User.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Foundation\\Auth\\User as Authenticatable;
use Illuminate\\Notifications\\Notifiable;
use Illuminate\\Sanctum\\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * A user can own many projects
     */
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Teams a user participates in
     */
    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class, 'dev_id');
    }

    /**
     * Activities assigned to this user
     */
    public function assignedActivities()
    {
        return $this->hasMany(Activity::class, 'asignado_a');
    }

    /**
     * Development logs by this user
     */
    public function devLogs()
    {
        return $this->hasMany(DevelopmentLog::class, 'developer_id');
    }

    /**
     * Products created by this user
     */
    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'creado_por');
    }
}
''')

# --- Project ---
w('app/Models/Project.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;
use Illuminate\\Database\\Eloquent\\Relations\\HasManyThrough;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany;
use Illuminate\\Database\\Eloquent\\Builder;

class Project extends Model
{
    use \\Illuminate\\Database\\Eloquent\\SoftDeletes;

    protected $fillable = [
        'name', 'description', 'git_repo_url', 'status',
        'start_date', 'end_date', 'owner_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- Relationships ---

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamAssigned(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'project_team_member', 'project_id', 'team_member_id');
    }

    // --- Accessors ---

    public function getProgressAttribute(): float
    {
        if (!$this->ende_date || !$this->start_date) {
            return 0.0;
        }

        $total = $this->requirements()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->requirements()->where('status', 'completado')->count();

        return round(($completed / $total) * 100, 2);
    }

    // --- Scopes ---

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['planificacion', 'en_desarrollo', 'en_pruebas']);
    }
}
''')

# --- Requirement ---
w('app/Models/Requirement.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Requirement extends Model
{
    use \\Illuminate\\Database\\Eloquent\\SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'description', 'priority', 'status',
        'estimated_hours', 'actual_hours',
    ];

    protected $casts = [
        'estimated_hours' => 'float',
        'actual_hours' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- Relationships ---

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(RequirementActa::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // --- Accessors ---

    public function getCompletionRateAttribute(): float
    {
        $activityCount = $this->activities()->count();
        if ($activityCount === 0) {
            return 0.0;
        }
        $doneCount = $this->activities()->where('status', 'completada')->count();
        return round(($doneCount / $activityCount) * 100, 2);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'alta' => 'High (Alta)',
            'media' => 'Medium (Media)',
            'baja' => 'Low (Baja)',
            default => ucfirst($this->priority ?? ''),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendiente' => 'Pending',
            'en_progreso' => 'In Progress',
            'completado' => 'Completed',
            'rechazado' => 'Rejected',
            default => ucfirst($this->status ?? ''),
        };
    }
}
''')

# --- RequirementActa ---
w('app/Models/RequirementActa.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class RequirementActa extends Model
{
    protected $table = 'requirement_actas';

    protected $fillable = [
        'requirement_id', 'fecha_sesion', 'cliente_nombre', 'cliente_email',
        'cliente_empresa', 'participantes', 'notas', 'firmas', 'acuerdos',
        'fecha_firma_acta', 'estado_firma',
    ];

    protected $casts = [
        'fecha_sesion' => 'date',
        'fecha_firma_acta' => 'datetime',
        'participantes' => 'array',
        'acuerdos' => 'array',
        'firmas' => 'string',
        'notas' => 'raw',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- Relationships ---

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
''')

# --- Activity ---
w('app/Models/Activity.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Activity extends Model
{
    use \\Illuminate\\Database\\Eloquent\\SoftDeletes;

    protected $fillable = [
        'requirement_id', 'title', 'description', 'status',
        'fecha_inicio_planificada', 'fecha_limite',
        'tiempo_estimado_horas', 'tiempo_real_horas',
        'asignado_a',
    ];

    protected $casts = [
        'fecha_inicio_planificada' => 'date',
        'fecha_limite' => 'date',
        'tiempo_estimado_horas' => 'float',
        'tiempo_real_horas' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- Relationships ---

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function developmentLogs(): HasMany
    {
        return $this->hasMany(DevelopmentLog::class);
    }

    public function assignedDeveloper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    // --- Accessors ---

    public function getOverdueAttribute(): bool
    {
        if (!$this->fecha_limite || $this->status === 'completada') {
            return false;
        }
        return now()->gt($this->fecha_limite);
    }

    public function getEfficiencyAttribute(): ?float
    {
        if ($this->tiempo_estimado_horas <= 0) {
            return null;
        }
        return round(($this->tiempo_estimado_horas / $this->tiempo_real_horas) * 100, 2);
    }
}
''')
w('app/Models/Project.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Builder;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;
use Illuminate\\Database\\Eloquent\\Relations\\HasManyThrough;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'name', 'description', 'git_repo_url', 'status',
        'start_date', 'end_date', 'owner_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // ---------- Relationships ----------

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamAssigned(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'project_team_member', 'project_id', 'team_member_id');
    }

    // ---------- Accessors ----------

    public function getProgresoAttribute(): float
    {
        $total = $this->requirements->count();
        if ($total === 0) {
            return 0.0;
        }
        $completed = $this->requirements->where('status', 'completado')->count();
        return round(($completed / $total) * 100, 2);
    }

    public function getRequirementsProgressAttribute(): float
    {
        return $this->requirements()->avg('completion_rate');
    }

    // ---------- Scopes ----------

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['planificacion', 'en_desarrollo', 'en_pruebas']);
    }

    public function scopeWithStats(Builder $query): Builder
    {
        return $query->withCount('requirements')
                     ->withCount(['activities as activities_count' => function ($q) {
                         $q->selectRaw('count(*)');
                     }]);
    }
}
''')

# ---- Requirement ----
w('app/Models/Requirement.php', '''<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Requirement extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'priority', 'status',
        'estimated_hours',
    ];

    protected $casts = [
        'estimated_hours' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(RequirementActa::class, 'requirement_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function getCompletionRateAttribute(): float
    {
        $total = $this->activities_count ?? 0;
        if ($total === 0) {
            return 0.0;
        }
        $done = $this->activities_count_done ?? 0;
        return round(($done / $total) * 100, 2);
    }

    public function activitiesCount()
    {
        return $this->hasMany(Activity::class)->count();
    }
}
''')
