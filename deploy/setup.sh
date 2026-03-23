#!/bin/bash
# =============================================================
#  setup.sh — Cài đặt LAMP stack trên Ubuntu (Oracle Cloud)
#  Chạy lần đầu sau khi SSH vào VPS
#  Usage: sudo bash setup.sh
# =============================================================

set -e  # Dừng nếu có lỗi
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

log()  { echo -e "${GREEN}[$(date '+%H:%M:%S')] ✓ $1${NC}"; }
warn() { echo -e "${YELLOW}[$(date '+%H:%M:%S')] ⚠ $1${NC}"; }
err()  { echo -e "${RED}[$(date '+%H:%M:%S')] ✗ $1${NC}"; exit 1; }

# ── Kiểm tra quyền root ──────────────────────────────────────
[[ $EUID -ne 0 ]] && err "Chạy script này với quyền sudo: sudo bash setup.sh"

DOMAIN="${1:-}"          # VD: sudo bash setup.sh tuanhuy.vn
DB_ROOT_PASS="${2:-TuanHuy@2025!}"
DB_NAME="tuanhuy_computer"
DB_USER="tuanhuy"
DB_PASS="${3:-TuanHuy_DB@2025!}"
PHP_VERSION="8.1"
PROJECT_DIR="/var/www/html/tuanhuy_computer"

echo "======================================================"
echo "  TuanHuy Computer — LAMP Stack Setup"
echo "  Domain: ${DOMAIN:-<chưa đặt>}"
echo "======================================================"
echo ""

# ── 1. Update hệ thống ───────────────────────────────────────
log "Cập nhật hệ thống..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq curl wget unzip git software-properties-common

# ── 2. Cài Apache2 ───────────────────────────────────────────
log "Cài đặt Apache2..."
apt-get install -y -qq apache2
systemctl enable apache2
systemctl start apache2

# ── 3. Cài PHP 8.1 ───────────────────────────────────────────
log "Thêm PHP 8.1 repository..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq

log "Cài đặt PHP ${PHP_VERSION} và extensions..."
apt-get install -y -qq \
    php${PHP_VERSION} \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-intl \
    libapache2-mod-php${PHP_VERSION}

# Đặt PHP 8.1 làm default
update-alternatives --set php /usr/bin/php${PHP_VERSION}

# ── 4. Cài MySQL 8.0 ─────────────────────────────────────────
log "Cài đặt MySQL 8.0..."
apt-get install -y -qq mysql-server mysql-client

systemctl enable mysql
systemctl start mysql

log "Cấu hình MySQL..."
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}';" 2>/dev/null || \
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}';"

mysql -u root -p"${DB_ROOT_PASS}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
log "Database '${DB_NAME}' và user '${DB_USER}' đã tạo xong."

# ── 5. Cấu hình PHP ──────────────────────────────────────────
log "Tối ưu cấu hình PHP..."
PHP_INI="/etc/php/${PHP_VERSION}/apache2/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 32M/' $PHP_INI
sed -i 's/post_max_size = .*/post_max_size = 32M/' $PHP_INI
sed -i 's/memory_limit = .*/memory_limit = 256M/' $PHP_INI
sed -i 's/max_execution_time = .*/max_execution_time = 120/' $PHP_INI
sed -i 's/display_errors = .*/display_errors = Off/' $PHP_INI
sed -i 's/;date.timezone.*/date.timezone = Asia\/Ho_Chi_Minh/' $PHP_INI

# ── 6. Tạo thư mục project ───────────────────────────────────
log "Tạo thư mục project..."
mkdir -p "${PROJECT_DIR}"
mkdir -p "${PROJECT_DIR}/storage"
mkdir -p "${PROJECT_DIR}/uploads/products"

# ── 7. Cấu hình Apache VirtualHost ──────────────────────────
log "Cấu hình Apache modules..."
a2enmod rewrite
a2enmod headers
a2enmod ssl
a2dissite 000-default.conf 2>/dev/null || true

log "Tạo VirtualHost..."
if [[ -n "$DOMAIN" ]]; then
    SERVER_NAME_CONF="ServerName ${DOMAIN}\n    ServerAlias www.${DOMAIN}"
    APP_URL_VAL="https://${DOMAIN}"
else
    SERVER_NAME_CONF="ServerName _"
    APP_URL_VAL="http://$(curl -s ifconfig.me)/tuanhuy_computer"
fi

cat > /etc/apache2/sites-available/tuanhuy_computer.conf <<EOF
<VirtualHost *:80>
    ${SERVER_NAME_CONF}
    DocumentRoot ${PROJECT_DIR}

    <Directory ${PROJECT_DIR}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/css application/json application/javascript
    </IfModule>

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"

    ErrorLog \${APACHE_LOG_DIR}/tuanhuy_error.log
    CustomLog \${APACHE_LOG_DIR}/tuanhuy_access.log combined
</VirtualHost>
EOF

a2ensite tuanhuy_computer.conf
systemctl restart apache2

# ── 8. Cấu hình Firewall ─────────────────────────────────────
log "Cấu hình firewall..."
# Oracle Cloud dùng iptables, không dùng ufw
iptables -I INPUT -p tcp --dport 80 -j ACCEPT
iptables -I INPUT -p tcp --dport 443 -j ACCEPT
iptables-save > /etc/iptables/rules.v4 2>/dev/null || \
    apt-get install -y -qq iptables-persistent && iptables-save > /etc/iptables/rules.v4

# ── 9. Lưu thông tin cấu hình ────────────────────────────────
cat > /root/db_credentials.txt <<EOF
=== Database Credentials ===
DB_NAME: ${DB_NAME}
DB_USER: ${DB_USER}
DB_PASS: ${DB_PASS}
DB_ROOT_PASS: ${DB_ROOT_PASS}
Generated: $(date)
EOF
chmod 600 /root/db_credentials.txt

# ── Tóm tắt ─────────────────────────────────────────────────
echo ""
echo "======================================================"
echo -e "  ${GREEN}✓ Cài đặt hoàn tất!${NC}"
echo "======================================================"
echo "  Apache  : $(apache2 -v 2>&1 | head -1)"
echo "  PHP     : $(php -v | head -1)"
echo "  MySQL   : $(mysql --version)"
echo ""
echo "  DB Name : ${DB_NAME}"
echo "  DB User : ${DB_USER}"
echo "  DB Pass : ${DB_PASS}"
echo ""
echo "  Thông tin đã lưu tại: /root/db_credentials.txt"
echo ""
echo "  Bước tiếp theo:"
echo "    bash deploy.sh"
echo "======================================================"
