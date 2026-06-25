FROM centos:7

# Fix CentOS 7 EOL mirrors — official mirrors are dead, use vault
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-*.repo && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*.repo

# Install Apache, PHP 5.4 (CentOS 7 default), and required extensions
RUN yum update -y && yum install -y \
    httpd \
    php \
    php-pdo \
    php-gd \
    php-mcrypt \
    php-mbstring \
    php-xml \
    php-mssql \
    freetds \
    unzip \
    zip \
    && yum clean all

# Configure FreeTDS for SQL Server connectivity
RUN printf "[global]\n\ttds version = 7.4\n\tclient charset = UTF-8\n" > /etc/freetds.conf

# Enable mod_rewrite AllowOverride for CodeIgniter .htaccess routing
RUN sed -i '/<Directory "\/var\/www\/html">/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/httpd/conf/httpd.conf

# Suppress Apache ServerName warning
RUN echo "ServerName localhost" >> /etc/httpd/conf/httpd.conf

# Fix PHP session settings for reverse-proxy (Cloudflare) environment
RUN { \
    echo 'session.cookie_secure = 0'; \
    echo 'session.cookie_httponly = 1'; \
    } > /etc/php.d/session-fix.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Ensure CodeIgniter cache, logs, and asset upload directories are writable and exist
RUN mkdir -p application/cache application/logs assets/import assets/attachments assets/profiles assets/esignatures \
    && chmod -R 777 application/cache application/logs assets/import assets/attachments assets/profiles assets/esignatures \
    && chown -R apache:apache /var/www/html

# Set environment variables
ENV PORT=9003
ENV DB_DRIVER="pdo"
ENV SESS_SAVE_PATH="/var/www/html/application/cache"

EXPOSE 9003

# Start Apache — dynamically set the listen port from the PORT env var
CMD ["bash", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-9003}/\" /etc/httpd/conf/httpd.conf && exec /usr/sbin/httpd -D FOREGROUND"]
