#!/bin/bash
# =============================================================
#  ssl.sh — Cài SSL miễn phí với Certbot (Let's Encrypt)
#  Usage: sudo bash ssl.sh your-domain.com [email]
# =============================================================

set -e
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

log()  { echo -e "${GREEN}[✓] $1${NC}"; }
warn() { echo -e "${YELLOW}[⚠] $1${NC}"; }
err()  { echo -e "${RED}[✗] $1${NC}"; exit 1; }

[[ $EUID -ne 0 ]] && err "Chạy với sudo: sudo bash ssl.sh your-domain.com"
[[ -z "$1" ]] && err "Cần nhập domain: sudo bash ssl.sh your-domain.com"

DOMAIN="$1"
EMAIL="${2:-admin@${DOMAIN}}"
PROJECT_DIR="/var/www/html/tuanhuy_computer"

echo "======================================================"
echo "  TuanHuy — SSL Certificate Setup"
echo "  Domain : ${DOMAIN}"
echo "  Email  : ${EMAIL}"
echo "======================================================"
echo ""

# ── Kiểm tra domain trỏ về máy chủ này chưa ─────────────────
SERVER_IP=$(curl -s ifconfig.me)
DOMAIN_IP=$(dig +short "${DOMAIN}" A 2>/dev/null | tail -1)

if [[ "$DOMAIN_IP" != "$SERVER_IP" ]]; then
    warn "Domain ${DOMAIN} chưa trỏ về IP ${SERVER_IP}"
    warn "Domain IP hiện tại: ${DOMAIN_IP:-<chưa resolve>}"
    warn "Certbot có thể thất bại nếu domain chưa trỏ đúng."
    read -p "Tiếp tục? (y/N) " CONT
    [[ "${CONT,,}" != "y" ]] && exit 0
fi

# ── Cài Certbot ──────────────────────────────────────────────
log "Cài đặt Certbot..."
apt-get install -y -qq snapd
snap install --classic certbot
ln -sf /snap/bin/certbot /usr/bin/certbot 2>/dev/null || true

# ── Lấy SSL certificate ──────────────────────────────────────
log "Lấy SSL certificate cho ${DOMAIN}..."
certbot --apache \
    -d "${DOMAIN}" \
    -d "www.${DOMAIN}" \
    --email "${EMAIL}" \
    --agree-tos \
    --non-interactive \
    --redirect

# ── Cập nhật APP_URL trong config ────────────────────────────
log "Cập nhật APP_URL sang HTTPS..."
sed -i "s|define('APP_URL'.*|define('APP_URL', 'https://${DOMAIN}');|" \
    "${PROJECT_DIR}/config/app.php"

# ── Cấu hình auto-renew ──────────────────────────────────────
log "Kiểm tra auto-renew..."
certbot renew --dry-run

# Thêm cron job renew nếu chưa có
CRON_JOB="0 3 * * * /usr/bin/certbot renew --quiet && systemctl reload apache2"
(crontab -l 2>/dev/null | grep -q 'certbot renew') || \
    (crontab -l 2>/dev/null; echo "${CRON_JOB}") | crontab -

log "Auto-renew đã cấu hình (chạy lúc 3:00 AM mỗi ngày)."

# ── Cấu hình HTTPS security headers ─────────────────────────
VHOST_CONF="/etc/apache2/sites-available/tuanhuy_computer-le-ssl.conf"
if [[ -f "$VHOST_CONF" ]]; then
    # Thêm HSTS nếu chưa có
    if ! grep -q 'Strict-Transport-Security' "$VHOST_CONF"; then
        sed -i '/<\/VirtualHost>/i\    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains"' "$VHOST_CONF"
    fi
fi

systemctl reload apache2

echo ""
echo "======================================================"
echo -e "  ${GREEN}✓ SSL cài đặt thành công!${NC}"
echo "======================================================"
echo "  URL       : https://${DOMAIN}"
echo "  Cert path : /etc/letsencrypt/live/${DOMAIN}/"
echo "  Hết hạn   : $(certbot certificates 2>/dev/null | grep 'Expiry Date' | head -1 | awk '{print $3,$4}')"
echo "  Auto-renew: ✓ (cron 3:00 AM)"
echo "======================================================"
