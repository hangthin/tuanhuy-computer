FROM php:8.1-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libzip-dev libonig-dev \
    libcurl4-openssl-dev libssl-dev \
    && docker-php-ext-install pdo_mysql curl mbstring gd zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Copy project
WORKDIR /var/www/html
COPY . .

# Remove Windows-only files
RUN rm -f start_bot.bat run_bot.vbs install_service.bat

# Apache config
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/tuanhuy.conf \
    && a2enconf tuanhuy

# Entrypoint: tạo config từ env vars rồi start Apache
COPY docker-entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r//' /entrypoint.sh && chmod +x /entrypoint.sh

# Permissions
RUN mkdir -p storage uploads/products \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["/entrypoint.sh"]
