# Dockerfile (Render-ready)
FROM php:8.3-apache

# เปิด mod_rewrite
RUN a2enmod rewrite

# ติดตั้ง Postgres PDO + psql client (สำหรับ seed DB)
RUN apt-get update \
 && apt-get install -y libpq-dev postgresql-client \
 && docker-php-ext-install pdo pdo_pgsql

# ปิด warning ServerName (optional)
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# คัดลอกโค้ดเข้า container
WORKDIR /var/www/html
COPY . /var/www/html

# ตั้ง permission (optional)
RUN chown -R www-data:www-data /var/www/html

# ให้ Apache ฟังตาม $PORT (Render จะตั้งให้)
ENV PORT=10000

# สคริปต์สตาร์ต: เขียน vhost ด้วย $PORT + (ทางเลือก) import SQL ครั้งแรก
RUN printf '#!/usr/bin/env bash\nset -e\np=${PORT:-10000}\n\
echo \"Listen ${p}\n<VirtualHost *:${p}>\n\
DocumentRoot /var/www/html/public\n\
<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n\
ErrorLog /proc/self/fd/2\nCustomLog /proc/self/fd/1 combined\n</VirtualHost>\" \
> /etc/apache2/sites-available/000-default.conf\n\
# ถ้ามีไฟล์ seed และ DATABASE_URL ให้ลอง import (ไม่เป็นไรถ้าซ้ำ)\n\
if [ -f /var/www/html/database/shop.pgsql ] && [ -n \"$DATABASE_URL\" ]; then\n\
  echo \"[initdb] importing database/shop.pgsql ...\" || true\n\
  psql \"$DATABASE_URL\" -f /var/www/html/database/shop.pgsql || true\n\
fi\n\
exec apache2-foreground\n' > /usr/local/bin/render-start.sh \
 && chmod +x /usr/local/bin/render-start.sh

EXPOSE 10000
CMD ["bash", "/usr/local/bin/render-start.sh"]
