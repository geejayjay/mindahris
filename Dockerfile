FROM php:7.4-apache

# Enable Apache mod_rewrite for CodeIgniter
RUN a2enmod rewrite

# Install dependencies for FreeTDS (SQL Server), mcrypt, and images
RUN apt-get update && apt-get install -y \
    freetds-dev \
    freetds-bin \
    libsybdb5 \
    libmcrypt-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure FreeTDS for SQL Server connectivity
RUN printf "[global]\n\ttds version = 7.4\n\tclient charset = UTF-8\n" > /etc/freetds/freetds.conf

# Install the legacy mcrypt extension via PECL
RUN pecl install mcrypt-1.0.4 \
    && docker-php-ext-enable mcrypt

# Configure and install pdo_dblib (SQL Server driver) and other required extensions
RUN docker-php-ext-configure pdo_dblib --with-libdir=/lib/x86_64-linux-gnu \
    && docker-php-ext-install pdo_dblib gd zip

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Ensure CodeIgniter cache and logs are writable
RUN mkdir -p application/cache application/logs \
    && chmod -R 777 application/cache application/logs \
    && chown -R www-data:www-data /var/www/html

# Update Apache to listen on the dynamic PORT provided by the hosting platform (defaulting to 9003)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Set environment variables
ENV CI_ENV=production
ENV PORT=9003

EXPOSE 9003
