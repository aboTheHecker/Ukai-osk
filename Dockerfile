FROM php:8.2-apache

# Disable extra MPM modules, enable only prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork rewrite

# Install mysqli extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\FROM php:8.2-apache

# Fix MPM conflict
RUN a2dismod mpm_event && a2enmod mpm_prefork && a2enmod rewrite

# Install mysqli extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80
    AllowOverride All\n\
</Directory>' >> /etc/apache2/apache2.conf