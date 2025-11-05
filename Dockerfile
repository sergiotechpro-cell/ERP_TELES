FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (skip scripts for now)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package files and necessary files for Vite build
COPY package.json package-lock.json ./
COPY vite.config.js ./
COPY resources ./resources
COPY tailwind.config.js postcss.config.js ./

# Install Node.js dependencies and build assets (need devDependencies for Vite)
RUN npm ci && npm run build && rm -rf node_modules

# Copy remaining application files
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Run post-install scripts now that files are available
RUN php artisan package:discover --ansi || true

# Set permissions
RUN chown -R www-data:www-data /app

# Expose port (Railway will set PORT env variable)
EXPOSE 8000

# Start Laravel server
CMD sh -c 'php artisan serve --host=0.0.0.0 --port=${PORT:-8000}'

