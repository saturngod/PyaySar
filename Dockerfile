FROM php:8.4-cli AS php-base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libicu-dev \
    libssl-dev libcurl4-openssl-dev libbrotli-dev \
    libpq-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip intl opcache

# Install Swoole (required for Octane)
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

FROM php-base AS build

# Build-only tooling is kept out of the runtime image.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files and install node dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Copy the rest of the application
COPY . .

# Generate autoloader, discover packages, and build frontend
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && npm run build \
    && rm -rf node_modules

FROM php-base AS runtime

WORKDIR /var/www/html

# Assign ownership while copying instead of recursively rewriting every file.
# node_modules was removed in the build stage and is not present in this image.
COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Only Laravel's runtime-writable paths need group write access.
RUN chmod 755 /usr/local/bin/entrypoint.sh \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8000

# Run migrations, cache config, then start supervisor
ENTRYPOINT ["entrypoint.sh"]
