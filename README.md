# README - Project Management API (Laravel)

## 📋 Descripción

Sistema backend de gestión de proyectos de software construido con Laravel 11. Proporciona una API REST completa con autenticación Sanctum, soporte para PostgreSQL y Redis, y capacidades de exportación a PDF y CSV.

## 🏗️ Arquitectura

### Stack Tecnológico
- **Framework**: Laravel 11 (PHP 8.3+)
- **Base de datos**: PostgreSQL
- **Cache/Queue**: Redis
- **Autenticación**: Laravel Sanctum
- **PDF**: DomPDF
- **ORM**: Eloquent

### Entidades Principales

```
Project
├── Requirements (N)
│   ├── RequirementActas (N)
│   └── Activities (N)
│       ├── Products (N)
│       └── DevelopmentLogs (N)
├── TeamMembers (N)
└── Activities (N) — también desde Project directamente
```

**Relaciones Obligatorias:**
| De | Para | Cardinalidad |
|----|------|-------------|
| Project | Requirement | 1-N |
| Requirement | RequirementActa | 1-N |
| Project | TeamMember | 1-N |
| Project | Activity | 1-N |
| Requirement | Activity | 1-N |
| Activity | Product | 1-N |
| Activity | DevelopmentLog | 1-N |

## 🚀 Instalación Rápida

### Opción 1: Docker (Recomendado)

```bash
# 1. Clonar y configurar
git clone <repo-url>
cd pm-api-laravel

# 2. Configurar variables
cp .env.example .env
# Editar .env con credenciales de DB y Redis

# 3. Iniciar con Docker
docker-compose up -d app db redis

# 4. Instalar dependencias
docker-compose exec app composer install

# 5. Migrar base de datos
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# 6. Verificar
curl http://localhost:8080/api/health
```

### Opción 2: Local

```bash
# 1. Requisitos: PHP 8.3+, Composer, PostgreSQL, Redis

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Migrar y semillas
php artisan migrate
php artisan db:seed

# 5. Iniciar servidor
php artisan serve
```

## 📡 Endpoints de la API

Todos los endpoints requieren autenticación Bearer token mediante Sanctum.

### Base URL: `/api/v1`

### Proyectos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/projects` | Listar proyectos (con filtros: status, search, page) |
| POST | `/projects` | Crear proyecto |
| GET | `/projects/{id}` | Ver proyecto |
| PUT | `/projects/{id}` | Actualizar proyecto |
| DELETE | `/projects/{id}` | Eliminar proyecto |
| GET | `/projects/{id}/stats` | Estadísticas del proyecto |

### Requerimientos (nested)
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET/POST | `/projects/{id}/requirements` | CRUD |

### Actas de Requerimientos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/requirements/{id}/actas` | Listar actas |
| POST | `/requirements/{id}/actas` | Crear acta |
| GET/PUT/DELETE | `/requirements/{id}/actas/{acta}` | Ver/Actualizar/Eliminar |

### Actividades
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET/POST | `/requirements/{id}/activities` | CRUD |

### Productos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET/POST | `/activities/{id}/products` | CRUD |

### Development Logs
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET/POST | `/activities/{id}/development-logs` | CRUD |

### Team Members
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET/POST | `/projects/{id}/team-members` | CRUD |

### Reportes/Exports
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/reports/summary` | Resumen global |
| GET | `/reports/projects/{id}` | Reporte JSON por proyecto |
| GET | `/reports/projects/{id}/pdf` | Exportar PDF por proyecto |
| GET | `/reports/projects/{id}/csv` | Exportar CSV por proyecto |
| GET | `/reports/developers/{id}` | Reporte JSON por desarrollador |
| GET | `/reports/developers/{id}/pdf` | Exportar PDF por desarrollador |
| GET | `/reports/team` | Reporte JSON del equipo |
| GET | `/reports/team/pdf` | Exportar PDF del equipo |

### Autenticación
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/login` | Login → token |
| POST | `/api/register` | Registro → token |
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Usuario actual |
| POST | `/api/refresh-token` | Renovar token |

## 🔐 Autenticación

El sistema usa Laravel Sanctum con tokens Bearer:

```bash
# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pmapi.local","password":"password"}'

# Respuesta:
# { "access_token": "1|abc123...", "token_type": "Bearer" }

# Uso
curl -H "Authorization: Bearer 1|abc123..." \
  http://localhost:8080/api/v1/projects
```

## 📊 Estados y Valores

### Proyectos
- `planificacion` | `en_desarrollo` | `en_pruebas` | `completado` | `cancelado`

### Requerimientos
- `pendiente` | `en_progreso` | `completado` | `rechazado`

### Actividades
- `pendiente` | `en_progreso` | `completada` | `bloqueada`

### Team Members
- `disponible` | `en_tarea` | `ocupado` | `fuera`

### Prioridades Requerimiento
- `alta` | `media` | `baja`

### Tipos de Dev Log
- `commit` | `fix` | `feature` | `review` | `deploy`

### Tipos de Producto
- `documento` | `codigo` | `diseno` | `testcase` | `configuracion`

### Estados de Firma
- `sin_firmar` | `esperando_firma` | `firmado` | `archivado`

## 🗄️ Estructura de Directorios

```
pm-api-laravel/
├── app/
│   ├── Http/Controllers/Api/    # Todos los controladores API
│   ├── Models/                   # Modelos Eloquent
│   └── Providers/               # Service providers
├── bootstrap/                   # Autoloading y app bootstrap
├── config/                      # Configuración Laravel
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Migraciones DB
│   └── seeders/                 # Seeders con datos de ejemplo
├── public/                      # Document root (index.php)
├── resources/views/pdf/         # Plantillas Blade para PDF
├── routes/
│   ├── api.php                  # Ruta de la API
│   ├── web.php                  # Rutas web
│   └── console.php              # Artisan commands
├── storage/                     # Archivos, logs, cache
├── tests/                       # Tests unitarios
├── Dockerfile                   # Docker imagen PHP
├── docker-compose.yml           # Orquestación servicios
├── composer.json               # Dependencias PHP
└── .env.example                # Variables de entorno
```

## 💻 Variables de Entorno

Ver `.env.example` para todas las variables configurables. Las principales:

```env
APP_NAME=PM API
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=pm_api
DB_USERNAME=postgres
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
```

## 🧪 Seeders

Al ejecutar `php artisan db:seed` se crean:
- **6-12 usuarios** (admin + developers)
- **5-15 proyectos** con datos realistas
- **15-20 requerimientos** con prioridades
- **4-8 actas** con firmas
- **10-15 actividades** asignadas a devs
- **15-20 productos** (código, docs, tests)
- **15-20 dev logs** con commits, fixes, features
- **12-18 team members** distribuidos por proyecto

## 🐳 Docker

### Iniciar servicios
```bash
docker-compose up -d
```

### Servicios incluidos
| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| app | 9000 (PHP-FPM) | Laravel API |
| db | 5432 | PostgreSQL |
| redis | 6379 | Redis Cache |
| phpmyadmin | 8088 | Panel DB (opcional) |

## ❓ Troubleshooting

**Error de clave de aplicación:**
```bash
php artisan key:generate
```

**Error de migraciones:**
Verificar conexión con `DB_HOST` correcto y base de datos existente.

**Error de permisos:**
```bash
chmod -R 775 storage/ bootstrap/cache/
```

**Verificar estado:**
```bash
docker-compose ps
php artisan route:list
php artisan config:clear
php artisan cache:clear
```
