#!/usr/bin/env python3
"""Create all missing Laravel infrastructure files."""
import os, stat

BASE = "/root/projects/pm-api-laravel"
created = []

def w(path, content, mode=None):
    full = os.path.join(BASE, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "w") as f:
        f.write(content)
    created.append(path)
    if mode:
        os.chmod(full, mode)

# ---- Storage directories ----
for d in ["framework/sessions", "framework/views", "framework/cache", "framework/testing", "logs"]:
    p = f"storage/{d}"
    os.makedirs(os.path.join(BASE, p), exist_ok=True)
    created.append(p + "/")

# ---- .docker/apache.conf ----
w(".docker/apache.conf", r"""<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>

DocumentRoot /var/www/html/public

<Directory /var/www/html/storage>
    Require all granted
</Directory>

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined
""")

# ---- tests/TestCase.php ----
w("tests/TestCase.php", r"""<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
""")

# ---- tests/Pest.php (optional but included for reference) ----
w("tests/Pest.php", r"""<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/
// Pest::extend(Tests\TestCase::class)->in('Feature');
""")

# ---- Ensure .htaccess supports public dir ----
w("public/.htaccess", r"""<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
""")

# ---- Make artisan executable ----
os.chmod(os.path.join(BASE, "artisan"), 0o755)

# ---- Create storage symlinks reference ----
w("storage/app/.gitkeep", "# Intentionally left blank - storage is gitignored\n")
w("storage/framework/.gitkeep", "# Intentionally left blank - framework files are generated at runtime\n")
w("storage/logs/.gitkeep", "# Intentionally left blank - logs are gitignored\n")

print(f"Created {len(created)} files & directories")
for item in created:
    print(f"  + {item}")

# ---- Verify final file tree ----
import subprocess
result = subprocess.run(["find", BASE, "-type", "f", "-not", "-path", "*/vendor/*", "-not", "-path", "*/.git/*", "-not", "-name", "*.py", "-not", "-name", "*.cache", "| wc -l"], capture_output=True, text=True)
total = result.stdout.strip()
print(f"\nTotal files in project: {total}")
