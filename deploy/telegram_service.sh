#!/bin/bash
# =============================================================
#  telegram_service.sh — Cài Telegram Bot như Linux systemd service
#  Bot sẽ tự khởi động khi máy chủ boot, chạy 24/7 không cần CMD
#  Usage: sudo bash telegram_service.sh
# =============================================================

set -e
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

log()  { echo -e "${GREEN}[✓] $1${NC}"; }
warn() { echo -e "${YELLOW}[⚠] $1${NC}"; }
err()  { echo -e "${RED}[✗] $1${NC}"; exit 1; }

[[ $EUID -ne 0 ]] && err "Chạy với sudo: sudo bash telegram_service.sh"

PROJECT_DIR="/var/www/html/tuanhuy_computer"
SERVICE_NAME="tuanhuy-telegram-bot"
PHP_BIN=$(which php8.1 2>/dev/null || which php 2>/dev/null)
DAEMON_SCRIPT="${PROJECT_DIR}/bot_daemon.php"

echo "======================================================"
echo "  TuanHuy — Telegram Bot Service Setup"
echo "======================================================"
echo "  PHP     : ${PHP_BIN}"
echo "  Script  : ${DAEMON_SCRIPT}"
echo ""

# ── Kiểm tra PHP và script ───────────────────────────────────
[[ -z "$PHP_BIN" ]] && err "Không tìm thấy PHP. Chạy setup.sh trước."
[[ ! -f "$DAEMON_SCRIPT" ]] && err "Không tìm thấy ${DAEMON_SCRIPT}"

# ── Kiểm tra token đã cấu hình chưa ─────────────────────────
TOKEN=$(grep "TELEGRAM_BOT_TOKEN" "${PROJECT_DIR}/config/app.php" | grep -oP "'[^']{20,}'" | tr -d "'" | head -1)
if [[ -z "$TOKEN" ]]; then
    warn "TELEGRAM_BOT_TOKEN chưa được điền trong config/app.php"
    warn "Bot service sẽ cài đặt nhưng không hoạt động cho đến khi điền token."
else
    log "Tìm thấy Telegram token: ...${TOKEN: -8}"
fi

# ── Tạo systemd service file ─────────────────────────────────
log "Tạo systemd service file..."

cat > /etc/systemd/system/${SERVICE_NAME}.service <<EOF
[Unit]
Description=TuanHuy Computer Telegram Bot
Documentation=https://github.com/tuanhuy/computer
After=network-online.target mysql.service apache2.service
Wants=network-online.target
Requires=mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=${PROJECT_DIR}
ExecStart=${PHP_BIN} ${DAEMON_SCRIPT}
Restart=always
RestartSec=5s
StartLimitIntervalSec=60
StartLimitBurst=5

# Logging
StandardOutput=append:${PROJECT_DIR}/storage/bot_service.log
StandardError=append:${PROJECT_DIR}/storage/bot_error.log

# Resource limits
MemoryMax=128M
CPUQuota=20%

# Environment
Environment=HOME=/var/www
Environment=PHP_CLI_SERVER_WORKERS=1

[Install]
WantedBy=multi-user.target
EOF

log "Tạo log files..."
mkdir -p "${PROJECT_DIR}/storage"
touch "${PROJECT_DIR}/storage/bot_service.log"
touch "${PROJECT_DIR}/storage/bot_error.log"
chown www-data:www-data "${PROJECT_DIR}/storage/bot_service.log"
chown www-data:www-data "${PROJECT_DIR}/storage/bot_error.log"

# ── Tạo logrotate để tránh log quá lớn ───────────────────────
cat > /etc/logrotate.d/${SERVICE_NAME} <<EOF
${PROJECT_DIR}/storage/bot_service.log
${PROJECT_DIR}/storage/bot_error.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 644 www-data www-data
}
EOF

# ── Enable và start service ──────────────────────────────────
log "Kích hoạt service..."
systemctl daemon-reload
systemctl enable ${SERVICE_NAME}
systemctl start ${SERVICE_NAME}

sleep 2

# ── Kiểm tra trạng thái ──────────────────────────────────────
if systemctl is-active --quiet ${SERVICE_NAME}; then
    log "Service đang chạy!"
else
    warn "Service chưa start được. Xem log:"
    journalctl -u ${SERVICE_NAME} -n 20 --no-pager
fi

echo ""
echo "======================================================"
echo -e "  ${GREEN}✓ Telegram Bot Service đã cài đặt!${NC}"
echo "======================================================"
echo ""
echo "  Quản lý service:"
echo "    Trạng thái : systemctl status ${SERVICE_NAME}"
echo "    Dừng       : systemctl stop ${SERVICE_NAME}"
echo "    Khởi động  : systemctl start ${SERVICE_NAME}"
echo "    Restart    : systemctl restart ${SERVICE_NAME}"
echo "    Xem log    : journalctl -u ${SERVICE_NAME} -f"
echo "    Log file   : tail -f ${PROJECT_DIR}/storage/bot_service.log"
echo ""
echo "  Auto-start khi boot: ✓ (đã bật)"
echo "======================================================"
