# Hướng dẫn Deploy lên Oracle Cloud (Free Tier)

> Oracle Cloud cung cấp **2 VM miễn phí mãi mãi** (Always Free) với 1GB RAM, 1 CPU, 50GB storage.

---

## Mục lục

1. [Tạo tài khoản Oracle Cloud](#1-tạo-tài-khoản-oracle-cloud)
2. [Tạo VM Instance](#2-tạo-vm-instance)
3. [Kết nối SSH](#3-kết-nối-ssh)
4. [Mở cổng Firewall](#4-mở-cổng-firewall)
5. [Chạy setup.sh](#5-chạy-setupsh)
6. [Upload project](#6-upload-project)
7. [Chạy deploy.sh](#7-chạy-deploysh)
8. [Cài SSL](#8-cài-ssl)
9. [Cài Telegram Bot Service](#9-cài-telegram-bot-service)
10. [Xử lý sự cố](#10-xử-lý-sự-cố)

---

## 1. Tạo tài khoản Oracle Cloud

1. Truy cập **https://cloud.oracle.com** → click **Start for free**
2. Điền thông tin:
   - **Country**: Vietnam
   - **Email**: email thật (cần xác nhận)
   - **Account type**: Individual
3. Cần thẻ **Visa/Mastercard** để xác minh (không bị trừ tiền nếu dùng Free Tier)
4. Sau khi đăng ký xong → vào **Oracle Cloud Console**

> ⚠️ **Lưu ý**: Quá trình xác minh có thể mất 1-2 ngày.

---

## 2. Tạo VM Instance

1. Trong Console → **Compute** → **Instances** → **Create Instance**

2. Cấu hình:
   | Mục | Chọn |
   |-----|------|
   | Name | `tuanhuy-server` |
   | Image | **Ubuntu 22.04** (Canonical Ubuntu) |
   | Shape | **VM.Standard.E2.1.Micro** (Always Free ✓) |
   | Network | Tạo mới VCN hoặc dùng mặc định |
   | Public IP | **Assign** (bật lên) |

3. **SSH Keys** — quan trọng:
   - Chọn **Generate a key pair for me**
   - Download cả **Private key** (.key) và **Public key** (.pub)
   - **Lưu file .key vào nơi an toàn**, mất là không SSH được

4. Click **Create** → đợi 2-3 phút cho instance chạy

5. Sau khi chạy → copy **Public IP Address** (VD: `150.230.xx.xx`)

---

## 3. Kết nối SSH

### Trên Windows (dùng PowerShell hoặc Terminal):

```powershell
# Di chuyển file key về thư mục .ssh
mkdir $HOME\.ssh
copy Downloads\ssh-key-xxxx.key $HOME\.ssh\oracle_key.pem

# Cấp quyền đọc (quan trọng)
icacls $HOME\.ssh\oracle_key.pem /inheritance:r /grant:r "$($env:USERNAME):(R)"

# Kết nối SSH
ssh -i $HOME\.ssh\oracle_key.pem ubuntu@150.230.xx.xx
```

### Hoặc dùng **MobaXterm** / **PuTTY** (giao diện):
1. Tải MobaXterm: https://mobaxterm.mobatek.net/download.html (Free)
2. New Session → SSH → điền IP → Advanced → Use private key → chọn file .key

---

## 4. Mở cổng Firewall

Oracle Cloud chặn port 80 và 443 theo mặc định. Cần mở 2 chỗ:

### 4.1 Security List (Oracle Console):

1. Console → **Networking** → **Virtual Cloud Networks** → chọn VCN
2. **Security Lists** → **Default Security List**
3. **Add Ingress Rules**:

| Source CIDR | IP Protocol | Destination Port |
|-------------|-------------|-----------------|
| 0.0.0.0/0 | TCP | 80 |
| 0.0.0.0/0 | TCP | 443 |

### 4.2 Ubuntu iptables (chạy trên SSH):

```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save
```

---

## 5. Chạy setup.sh

Sau khi SSH vào máy chủ:

```bash
# Tạo thư mục deploy
mkdir -p ~/deploy
cd ~/deploy
```

**Cách 1: Upload file setup.sh từ máy local:**
```powershell
# Chạy trên máy Windows (PowerShell)
scp -i $HOME\.ssh\oracle_key.pem `
    C:\AppServ\www\tuanhuy_computer\deploy\setup.sh `
    ubuntu@150.230.xx.xx:~/deploy/
```

**Cách 2: Copy paste nội dung trực tiếp vào SSH.**

```bash
# Trên máy chủ — chạy setup
cd ~/deploy
chmod +x setup.sh

# Nếu có domain:
sudo bash setup.sh yourdomain.com

# Nếu chưa có domain (dùng IP):
sudo bash setup.sh
```

Quá trình cài đặt mất khoảng **5-10 phút**. Kết quả cuối hiện thông tin DB.

---

## 6. Upload project

Chạy trên **máy Windows** (PowerShell):

```powershell
# Nén project (bỏ qua .git và node_modules)
cd C:\AppServ\www
tar -czf tuanhuy_computer.tar.gz `
    --exclude=tuanhuy_computer/.git `
    --exclude=tuanhuy_computer/node_modules `
    tuanhuy_computer/

# Upload lên server
scp -i $HOME\.ssh\oracle_key.pem `
    tuanhuy_computer.tar.gz `
    ubuntu@150.230.xx.xx:~/

# SSH vào server và giải nén
ssh -i $HOME\.ssh\oracle_key.pem ubuntu@150.230.xx.xx
```

Trên máy chủ:
```bash
# Giải nén vào đúng vị trí
sudo tar -xzf ~/tuanhuy_computer.tar.gz -C /var/www/html/
sudo chown -R ubuntu:ubuntu /var/www/html/tuanhuy_computer
ls /var/www/html/tuanhuy_computer  # kiểm tra files có đủ không
```

---

## 7. Chạy deploy.sh

```bash
cd /var/www/html/tuanhuy_computer/deploy
chmod +x deploy.sh

sudo bash deploy.sh
```

Script sẽ tự động:
- ✓ Backup nếu có version cũ
- ✓ Cập nhật `config/app.php` với APP_URL đúng
- ✓ Import database từ `database/migrations.sql`
- ✓ Cấp permissions đúng cho www-data
- ✓ Tạo `.htaccess` bảo mật cho production

**Kiểm tra website:**
```
http://150.230.xx.xx
```
Thấy trang chủ là thành công!

---

## 8. Cài SSL

> Yêu cầu: domain đã trỏ về IP máy chủ (DNS propagate xong)

```bash
cd /var/www/html/tuanhuy_computer/deploy
chmod +x ssl.sh

# Thay yourdomain.com bằng domain thật
sudo bash ssl.sh yourdomain.com admin@yourdomain.com
```

Sau khi xong, website tự động redirect sang HTTPS:
```
https://yourdomain.com
```

**Kiểm tra SSL:**
```bash
certbot certificates
```

---

## 9. Cài Telegram Bot Service

```bash
cd /var/www/html/tuanhuy_computer/deploy
chmod +x telegram_service.sh

sudo bash telegram_service.sh
```

**Kiểm tra bot đang chạy:**
```bash
systemctl status tuanhuy-telegram-bot
```

**Xem log realtime:**
```bash
journalctl -u tuanhuy-telegram-bot -f
# hoặc
tail -f /var/www/html/tuanhuy_computer/storage/bot_service.log
```

Bot sẽ **tự động khởi động** mỗi khi server reboot. Gửi tin nhắn cho `@TuanHuyComputerBot` để test.

---

## 10. Xử lý sự cố

### Website không load được:
```bash
# Kiểm tra Apache
sudo systemctl status apache2
sudo tail -50 /var/log/apache2/tuanhuy_error.log

# Kiểm tra PHP
php -v

# Test Apache config
sudo apachectl configtest
```

### Lỗi 500:
```bash
sudo tail -50 /var/log/apache2/tuanhuy_error.log
```

### Database không kết nối:
```bash
# Kiểm tra MySQL
sudo systemctl status mysql

# Test kết nối
mysql -u tuanhuy -p tuanhuy_computer
```

### Bot Telegram không phản hồi:
```bash
# Restart bot
sudo systemctl restart tuanhuy-telegram-bot

# Xem lỗi
sudo journalctl -u tuanhuy-telegram-bot -n 50

# Kiểm tra token
grep TELEGRAM /var/www/html/tuanhuy_computer/config/app.php
```

### Cổng 80/443 không truy cập được:
```bash
# Kiểm tra iptables
sudo iptables -L INPUT -n --line-numbers

# Mở cổng nếu chưa mở
sudo iptables -I INPUT 6 -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save
```

---

## Tóm tắt các lệnh thường dùng

```bash
# Restart tất cả services
sudo systemctl restart apache2 mysql tuanhuy-telegram-bot

# Xem log Apache realtime
sudo tail -f /var/log/apache2/tuanhuy_error.log

# Deploy lại (khi có code mới)
cd /var/www/html/tuanhuy_computer/deploy && sudo bash deploy.sh

# Backup database thủ công
mysqldump -u tuanhuy -p tuanhuy_computer > ~/backup_$(date +%Y%m%d).sql

# Kiểm tra dung lượng
df -h
du -sh /var/www/html/tuanhuy_computer
```

---

*Tài liệu này được tạo cho dự án TuanHuy Computer E-commerce.*
