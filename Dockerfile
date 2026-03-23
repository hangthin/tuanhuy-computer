FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libzip-dev libonig-dev \
    libcurl4-openssl-dev libssl-dev \
    && docker-php-ext-install pdo_mysql curl mbstring gd zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

RUN rm -f start_bot.bat run_bot.vbs install_service.bat

RUN echo '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>' \
    > /etc/apache2/conf-available/tuanhuy.conf && a2enconf tuanhuy

RUN mkdir -p storage uploads/products \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
