# Dockerfile (for PHP plain + PostgreSQL on Render)
FROM php:8.3-apache

# เปิด mod_rewrite และติดตั้ง Postgres PDO + psql client
RUN a2enmod rewrite \
 && apt-get update \
 && apt-get install -y libpq-dev postgresql-client \
 && docker-php-ext-install pdo pdo_pgsql

# ปิด warning ServerName (optional)
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# โค้ดเว็บอยู่ที่ /var/www/html (DocumentRoot ของ apache ดีฟอลต์ก็ตรงรูทนี้)
WORKDIR /var/www/html
COPY . /var/www/html

# สิทธิ์ไฟล์ (optional)
RUN chown -R www-data:www-data /var/www/html

# ให้ .htaccess ใช้ได้ (AllowOverride All) และเปิดสิทธิ์โฟลเดอร์เว็บ
RUN printf "\n<Directory /var/www/html>\n  AllowOverride All\n  Require all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

# Render จะตั้ง $PORT ให้ (ดีฟอลต์ 10000)
ENV PORT=10000
EXPOSE 10000

# ปรับ Apache ให้ฟังพอร์ต $PORT ตอนรัน + (ออปชัน) seed DB รถวเดียว
CMD ["bash", "-lc", "\
p=${PORT:-10000}; \
sed -ri 's/Listen [0-9]+/Listen '\"$p\"'/' /etc/apache2/ports.conf; \
sed -ri 's#<VirtualHost \\*:[0-9]+>#<VirtualHost *:'\"$p\"'>#' /etc/apache2/sites-available/000-default.conf; \
if [ -f /var/www/html/database/shop.pgsql ] && [ -n \"${DATABASE_URL:-}\" ]; then \
  echo '[initdb] importing database/shop.pgsql ...'; \
  psql \"${DATABASE_URL}\" -f /var/www/html/database/shop.pgsql || true; \
fi; \
exec apache2-foreground"]
