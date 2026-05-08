FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-sqlite3 \
    php8.1-pdo \
    libapache2-mod-php8.1 \
    && apt-get clean

RUN a2dismod mpm_event && a2enmod mpm_prefork php8.1

RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/mods-enabled/dir.conf

# Mostrar errores PHP para poder diagnosticar
RUN echo "display_errors = On" >> /etc/php/8.1/apache2/php.ini \
    && echo "error_reporting = E_ALL" >> /etc/php/8.1/apache2/php.ini

# Suprimir advertencia de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Corregir permisos de sesiones PHP
RUN chown www-data:www-data /var/lib/php/sessions \
    && chmod 777 /var/lib/php/sessions

RUN rm -f /var/www/html/index.html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 777 /var/www/html \
    && chmod 666 /var/www/html/usuarios.db 2>/dev/null || true

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
