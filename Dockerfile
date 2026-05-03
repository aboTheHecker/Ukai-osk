FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    gettext-base \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql mysqli zip

COPY . /var/www/html/

RUN echo 'server { \
    listen ${PORT}; \
    root /var/www/html; \
    index index.php index.html; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { \
    fastcgi_pass 127.0.0.1:9000; \
    fastcgi_index index.php; \
    include fastcgi_params; \
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
    }' > /etc/nginx/sites-available/default

RUN chown -R www-data:www-data /var/www/html

CMD bash -c "envsubst '\$PORT' < /etc/nginx/sites-available/default > /etc/nginx/sites-enabled/default && php-fpm -D && nginx -g 'daemon off;'"