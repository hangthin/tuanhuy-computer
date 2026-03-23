#!/bin/bash
# =============================================================
#  deploy.sh — Upload và cấu hình project lên VPS
#  Chạy từ máy local HOẶC trực tiếp trên VPS
#  Usage: bash deploy.sh [--production]
# =============================================================

set -e
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'

log()  { echo -e "${GREEN}[$(date '+%H:%M:%S')] ✓ $1${NC}"; }
warn() { echo -e "${YELLOW}[$(date '+%H:%M:%S')] ⚠ $1${NC}"; }
info() { echo -e "${CYAN}[$(date '+%H:%M:%S')] ℹ $1${NC}"; }
err()  { echo -e "${RED}[$(date '+%H:%M:%S')] ✗ $1${NC}"; exit 1; }

PROJECT_DIR="/var/www/html/tuanhuy_computer"
DB_NAME="tuanhuy_computer"
BACKUP_DIR="/var/backups/tuanhuy"
IS_PRODUCTION="${1:-}"

# ── Đọc DB credentials ───────────────────────────────────────
if [[ -f /root/db_credentials.txt ]]; then
    DB_USER=$(grep 'DB_USER' /root/db_credentials.txt | cut -d' ' -f2)
    DB_PASS=$(grep 'DB_PASS:' /root/db_credentials.txt | head -1 | cut -d' ' -f2)
    DB_ROOT_PASS=$(grep 'DB_ROOT_PASS' /root/db_credentials.txt | cut -d' ' -f2)
else
    warn "Không tìm thấy /root/db_credentials.txt"
    read -p "DB Root Password: " DB_ROOT_PASS
    read -p "DB User: " DB_USER
    read -p "DB Password: " DB_PASS
fi

echo "======================================================"
echo "  TuanHuy Computer — Deploy Script"
echo "  Target: ${PROJECT_DIR}"
echo "======================================================"

# ── 1. Backup nếu đã có version cũ ──────────────────────────
if [[ -d "${PROJECT_DIR}" && "$(ls -A ${PROJECT_DIR} 2>/dev/null)" ]]; then
    log "Backup version cũ..."
    mkdir -p "${BACKUP_DIR}"
    BACKUP_FILE="${BACKUP_DIR}/backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    tar -czf "${BACKUP_FILE}" -C "$(dirname ${PROJECT_DIR})" "$(basename ${PROJECT_DIR})" 2>/dev/null || true
    log "Backup tại: ${BACKUP_FILE}"

    # Backup database
    log "Backup database..."
    mysqldump -u root -p"${DB_ROOT_PASS}" "${DB_NAME}" > "${BACKUP_DIR}/db_$(date +%Y%m%d_%H%M%S).sql" 2>/dev/null || warn "Không backup được DB"
fi

# ── 2. Copy files (nếu chạy trên VPS với source đã có) ───────
if [[ -f "$(dirname $0)/../index.php" ]]; then
    SOURCE_DIR="$(dirname $0)/.."
    log "Copy project files từ ${SOURCE_DIR}..."
    rsync -a --exclude='.git' \
              --exclude='node_modules' \
              --exclude='deploy' \
              --exclude='*.bat' \
              --exclude='*.vbs' \
              --exclude='start_bot.bat' \
              --exclude='storage/*.log' \
              --exclude='storage/telegram_offset.txt' \
              "${SOURCE_DIR}/" "${PROJECT_DIR}/"
else
    warn "Không tìm thấy source dir. Đảm bảo project đã upload lên ${PROJECT_DIR}"
fi

# ── 3. Tạo thư mục cần thiết ─────────────────────────────────
log "Tạo thư mục storage, uploads..."
mkdir -p "${PROJECT_DIR}/storage"
mkdir -p "${PROJECT_DIR}/uploads/products"
mkdir -p "${PROJECT_DIR}/uploads/tmp"

# ── 4. Cấu hình config/app.php cho production ────────────────
log "Cấu hình production config..."

# Lấy IP public hoặc domain
if [[ -f /etc/apache2/sites-enabled/tuanhuy_computer.conf ]]; then
    DOMAIN=$(grep 'ServerName' /etc/apache2/sites-enabled/tuanhuy_computer.conf | grep -v '_' | awk '{print $2}' | head -1)
fi

if [[ -n "$DOMAIN" ]]; then
    APP_URL="https://${DOMAIN}"
else
    PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "YOUR_SERVER_IP")
    APP_URL="http://${PUBLIC_IP}"
    warn "Chưa có domain. APP_URL = ${APP_URL}"
    warn "Cập nhật lại sau khi cấu hình SSL và domain."
fi

APP_PHP="${PROJECT_DIR}/config/app.php"
if [[ -f "$APP_PHP" ]]; then
    # Cập nhật APP_URL
    sed -i "s|define('APP_URL'.*|define('APP_URL', '${APP_URL}');|" "$APP_PHP"
    # Tắt display_errors trên production
    sed -i "s|ini_set('display_errors', 1)|ini_set('display_errors', 0)|" "$APP_PHP"
    log "APP_URL = ${APP_URL}"
fi

# ── 5. Cấu hình database connection ──────────────────────────
DB_PHP="${PROJECT_DIR}/config/database.php"
if [[ -f "$DB_PHP" ]]; then
    log "Cập nhật database config..."
    sed -i "s/'host'.*=>.*'localhost'/'host' => 'localhost'/" "$DB_PHP"
    sed -i "s/'dbname'.*=>.*'[^']*'/'dbname' => '${DB_NAME}'/" "$DB_PHP"
    sed -i "s/'username'.*=>.*'[^']*'/'username' => '${DB_USER}'/" "$DB_PHP"
    sed -i "s/'password'.*=>.*'[^']*'/'password' => '${DB_PASS}'/" "$DB_PHP"
fi

# ── 6. Import database ───────────────────────────────────────
SQL_FILE="${PROJECT_DIR}/database/migrations.sql"
if [[ -f "$SQL_FILE" ]]; then
    log "Import database schema..."
    mysql -u root -p"${DB_ROOT_PASS}" "${DB_NAME}" < "$SQL_FILE"
    log "Database import xong!"
else
    warn "Không tìm thấy ${SQL_FILE}"
fi

# ── 7. Phân quyền files ──────────────────────────────────────
log "Cài đặt permissions..."
chown -R www-data:www-data "${PROJECT_DIR}"
find "${PROJECT_DIR}" -type f -exec chmod 644 {} \;
find "${PROJECT_DIR}" -type d -exec chmod 755 {} \;
# Cho phép write vào storage và uploads
chmod -R 775 "${PROJECT_DIR}/storage"
chmod -R 775 "${PROJECT_DIR}/uploads"
# Bảo vệ config
chmod 640 "${PROJECT_DIR}/config/app.php"
chmod 640 "${PROJECT_DIR}/config/database.php"

# ── 8. Tạo .htaccess bảo vệ ─────────────────────────────────
log "Tạo .htaccess cho storage..."
cat > "${PROJECT_DIR}/storage/.htaccess" <<'HTACCESS'
Order Deny,Allow
Deny from all
HTACCESS

# ── 9. Tạo .htaccess production cho root ─────────────────────
cat > "${PROJECT_DIR}/.htaccess" <<'HTACCESS'
Options -Indexes
RewriteEngine On
RewriteBase /

# Chặn truy cập file nhạy cảm
RewriteRule ^(config|deploy|database|storage)/.*$ - [F,L]
RewriteRule ^(bot_daemon|bot_tick|bot_run|run_bot)\.php$ - [F,L]
RewriteRule ^.*\.bat$ - [F,L]
RewriteRule ^.*\.vbs$ - [F,L]

# Route qua index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Bảo mật headers
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
HTACCESS

chown www-data:www-data "${PROJECT_DIR}/.htaccess"

# ── 10. Restart services ─────────────────────────────────────
log "Restart Apache..."
systemctl reload apache2

# ── Tóm tắt ─────────────────────────────────────────────────
echo ""
echo "======================================================"
echo -e "  ${GREEN}✓ Deploy hoàn tất!${NC}"
echo "======================================================"
echo "  URL     : ${APP_URL}"
echo "  Dir     : ${PROJECT_DIR}"
echo ""
echo "  Bước tiếp theo:"
if [[ -z "$DOMAIN" ]]; then
    echo "    1. Trỏ domain về IP: ${PUBLIC_IP:-<IP máy chủ>}"
    echo "    2. bash ssl.sh your-domain.com"
else
    echo "    bash ssl.sh ${DOMAIN}"
fi
echo "    bash telegram_service.sh"
echo "======================================================"
