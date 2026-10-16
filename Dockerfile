FROM php:8.2-apache

# Hardening de Apache: Ocultar información del servidor
RUN sed -i 's/ServerTokens OS/ServerTokens Prod/' /etc/apache2/conf-available/security.conf && \
    sed -i 's/ServerSignature On/ServerSignature Off/' /etc/apache2/conf-available/security.conf

# Hardening de PHP para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    echo "expose_php = Off" >> "$PHP_INI_DIR/php.ini" && \
    echo "display_errors = Off" >> "$PHP_INI_DIR/php.ini"

# Instalación segura de extensiones de base de datos y limpieza de temporales
RUN apt-get update && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

# Permisos para el usuario sin privilegios www-data
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://localhost/ || exit 1

EXPOSE 80