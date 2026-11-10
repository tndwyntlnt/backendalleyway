FROM php:8.2-apache

# Install dependencies yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpq-dev

# Install ekstensi PHP (termasuk PDO PostgreSQL untuk Supabase)
RUN docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy semua file proyek ke dalam container
COPY . .

# Install dependency Laravel menggunakan Composer
RUN composer install --no-dev --optimize-autoloader

# Ubah DocumentRoot Apache agar mengarah ke folder /public Laravel
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Set hak akses untuk folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache