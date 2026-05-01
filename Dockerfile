FROM php:8.2-apache

# Force only mpm_prefork
RUN apt-get update && apt-get install -y apache2 && \
    a2dismod mpm_event mpm_worker || true && \
    a2enmod mpm_prefork rewrite

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Allow .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]