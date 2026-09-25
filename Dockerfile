FROM php:7.4-fpm-alpine

# Instalar dependencias del sistema y librerías necesarias para Drupal 7
RUN apk add --no-cache \
    nginx \
    supervisor \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    mariadb-client

# Configurar e instalar extensiones de PHP necesarias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        mysqli \
        opcache \
        zip \
        intl

# Configuración de Nginx
COPY nginx.conf /etc/nginx/http.d/default.conf

# Configuración de Supervisor para mantener Nginx y PHP-FPM corriendo
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configuración de PHP (Opcional: ajustar límites)
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/docker-php-ram.ini

WORKDIR /var/www/html

# Copiar el código del proyecto (asegúrate de que la carpeta 'web' o el código esté presente)
COPY . /var/www/html

# Crear directorio para archivos temporales y dar permisos
RUN mkdir -p /var/www/html/sites/default/files \
    && chown -R www-data:www-data /var/www/html/sites/default/files

# Asegurar que PHP-FPM escuche en 127.0.0.1:9000
RUN sed -i 's/listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
