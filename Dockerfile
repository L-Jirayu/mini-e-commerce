FROM php:8.3-apache

# เปิด mod_rewrite (optional แต่มีไว้ก็ดี)
RUN a2enmod rewrite

# ติดตั้ง PostgreSQL PDO
RUN apt-get update \
 && apt-get install -y libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql

# ปิด warning ServerName (optional)
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# คัดลอกโค้ดเข้า container
COPY . /var/www/html/

# ตั้ง permission (optional)
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
EXPOSE 80
