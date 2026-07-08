FROM php:8.3-fpm-alpine3.20

# Install dependencies
RUN apk add --no-cache \
    nginx \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    redis-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Configure Nginx
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && mkdir -p /var/www/html/storage/framework/{sessions,views,cache,packages} \
    && chmod -R 775 /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache

# Nginx config for Laravel
RUN cat > /etc/nginx/http.d/default.conf <<'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

# Health check
RUN mkdir -p /healthcheck && echo "<?php echo 'OK'; ?>" > /healthcheck/index.php

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
