# Tuấn Huy Computer

Website thương mại điện tử bán linh kiện máy tính, xây dựng bằng PHP thuần + MySQL. Không dùng framework, không Composer, không npm.

**Local:** `http://localhost/tuanhuy_computer`  
**Production:** Apache trên EC2 (Ubuntu)

---

## Tính năng

**Khách hàng**
- Duyệt sản phẩm theo danh mục, tìm kiếm, lọc theo giá / thương hiệu / trạng thái
- Giỏ hàng, mã giảm giá, đặt hàng, thanh toán chuyển khoản / MoMo
- Theo dõi đơn hàng, hủy đơn, đánh giá sản phẩm
- Đăng nhập / đăng ký, Google OAuth

**Admin / Staff**
- Quản lý sản phẩm, danh mục, đơn hàng, khách hàng, mã giảm giá, banner
- AI tạo tên & mô tả sản phẩm (Groq), tìm ảnh (SerpApi / Bing / Pexels / Pixabay), xóa nền ảnh (browser-side ESM)
- Audit log toàn bộ thao tác
- Thông báo Telegram theo thời gian thực
- Phân quyền 3 cấp: Admin / Manager / Staff

---

## Tech Stack

| Thành phần | Chi tiết |
|---|---|
| Backend | PHP 7.4+ / 8.1, không framework |
| Database | MySQL 5.7+ / 8.0 |
| Web server | Apache + `mod_rewrite` (AppServ / XAMPP local, Apache2 production) |
| AI | Groq API — `llama-3.2-11b-vision-preview` |
| Image search | SerpApi → Bing → Pexels → Pixabay → Google fallback |
| Background removal | `@imgly/background-removal` (ESM từ esm.sh, chạy trên browser) |
| Notification | Telegram Bot API |
| Email | SMTP Gmail |
| Auth bên thứ 3 | Google OAuth 2.0 |
| Payment UI | Techcombank VietQR, MoMo deeplink |

---

## Cài đặt Local (AppServ / XAMPP)

### Yêu cầu
- AppServ 2.6+ hoặc XAMPP với PHP 7.4+ và MySQL
- `mod_rewrite` bật trong Apache

### Các bước

```bash
# 1. Clone vào web root
git clone https://github.com/hangthin/tuanhuy-computer.git C:/AppServ/www/tuanhuy_computer

# 2. Tạo database
# Mở phpMyAdmin → tạo DB tên "mpc" → import
mysql -u root -p mpc < database/migrations.sql
```

```bash
# 3. Tạo file cấu hình môi trường
cp .env.local.example .env.local   # hoặc tạo mới (xem mẫu bên dưới)
```

```bash
# 4. Tạo thư mục cần thiết
mkdir -p uploads/products storage
```

Truy cập: `http://localhost/tuanhuy_computer`

---

## Cấu hình

### `.env.local` (local overrides — không commit)

```env
APP_URL=http://localhost/tuanhuy_computer

DB_HOST=localhost
DB_NAME=mpc
DB_USER=root
DB_PASS=

AI_API_KEY=gsk_...          # Groq API key
MAIL_USER=you@gmail.com
MAIL_PASS=xxxx xxxx xxxx xxxx   # Gmail App Password
TELEGRAM_BOT_TOKEN=123456:ABC...
TELEGRAM_ADMIN_CHAT=7329986368
```

### `config/app.php` — hằng số toàn cục

| Hằng số | Mô tả |
|---|---|
| `APP_URL` | Base URL của site |
| `AI_API_KEY` | Groq API key |
| `TELEGRAM_BOT_TOKEN` | Token Telegram bot |
| `TELEGRAM_ADMIN_CHAT` | Chat ID nhận thông báo |
| `TELEGRAM_CRON_SECRET` | Token bảo vệ endpoint cron |
| `MAIL_USER / MAIL_PASS` | Gmail + App Password |
| `GOOGLE_CLIENT_ID/SECRET` | Google OAuth credentials |
| `SERPAPI_KEY` | Tìm ảnh qua SerpApi |
| `BING_SEARCH_KEY` | Bing Image Search |
| `REMOVEBG_KEY` | remove.bg API (fallback server-side) |
| `BANK_NO` | Số tài khoản Techcombank |
| `MOMO_NO` | Số điện thoại MoMo |

`display_errors` tự bật khi `APP_URL` chứa `localhost`, tắt trên production.

### `config/database.php`

```php
// Chỉnh trực tiếp nếu không dùng .env.local
'host'     => 'localhost',
'dbname'   => 'mpc',
'username' => 'root',
'password' => '',
```

---

## Deploy lên EC2 (Ubuntu + Apache2)

### Lần đầu

```bash
# Trên máy local — copy project lên server
scp -i key.pem -r . ubuntu@<EC2_IP>:/var/www/html/tuanhuy_computer

# Trên server
cd /var/www/html/tuanhuy_computer
bash deploy/setup.sh          # cài Apache, PHP, MySQL
bash deploy/deploy.sh         # config, import DB, set permissions
bash deploy/ssl.sh your-domain.com   # Let's Encrypt (cần domain trỏ về IP)
```

### Cập nhật

```bash
# Từ máy local
bash deploy/sync_server.sh    # rsync lên server (cần key.pem)

# Hoặc trực tiếp trên server
cd /var/www/html/tuanhuy_computer
git pull origin main
systemctl reload apache2
```

### Biến môi trường trên server

Tạo `/etc/apache2/sites-available/tuanhuy_computer.conf` và thêm:

```apache
SetEnv APP_URL "https://your-domain.com"
SetEnv AI_API_KEY "gsk_..."
SetEnv TELEGRAM_BOT_TOKEN "..."
SetEnv MAIL_USER "..."
SetEnv MAIL_PASS "..."
```

---

## Cấu trúc thư mục

```
tuanhuy_computer/
├── index.php               # Router chính
├── .htaccess               # Rewrite rules
├── .env.local              # Biến môi trường local (không commit)
├── config/
│   ├── app.php             # Hằng số + helper functions
│   └── database.php        # Singleton PDO
├── app/
│   ├── Controllers/        # AccountController, AdminController, ApiController, ...
│   ├── Models/
│   │   ├── Models.php      # User, Cart, Order, Category
│   │   └── ProductModel.php
│   ├── Views/
│   │   ├── admin/          # Giao diện quản trị
│   │   ├── home/           # Trang chủ
│   │   ├── products/       # Danh sách & chi tiết sản phẩm
│   │   ├── auth/           # Login / Register
│   │   ├── cart/ checkout/ account/
│   │   └── layouts/        # header.php, footer.php
│   ├── Helpers/            # AITools, TelegramBot, Mailer, ...
│   └── Middleware/
│       └── RoleGuard.php   # Phân quyền Admin/Manager/Staff
├── assets/
│   └── images/             # Logo, ảnh tĩnh
├── uploads/
│   └── products/           # Ảnh sản phẩm (không commit)
├── storage/                # Cache, log, JSON state (không commit)
├── database/
│   └── migrations.sql      # Schema đầy đủ + migrations gộp
├── deploy/                 # Scripts deploy lên EC2
└── scripts/                # Tiện ích Python (fix data, remove bg)
```

---

## Routing

Mọi request đều qua `index.php` (via `.htaccess`).

```
/{controller}/{action}/{param}
```

- Kebab-case → camelCase: `cancel-order` → `cancelOrder()`
- `/products/{slug}` → `ProductController->index($slug)`
- `/api/{action}/{param}` → `ApiController->{action}($param)`
- `/admin/ai/generator` → `AdminController->ai('generator')`

---

## Đăng nhập Admin

1. Truy cập `http://localhost/tuanhuy_computer/auth/login` (hoặc nhấn **Đăng nhập** trên header)
2. Đăng nhập bằng tài khoản Admin:

| Role | Email | Mật khẩu |
|---|---|---|
| Admin | admin@tuanhuycomputer.com | `admin123` |

3. Sau khi đăng nhập, vào trang quản trị tại: `/admin`

> **Quan trọng:** Đổi mật khẩu ngay sau khi deploy lần đầu tại `/admin/staff`.

---

## Các trang Admin

| URL | Chức năng |
|---|---|
| `/admin` | Dashboard — doanh thu, đơn hàng mới, sản phẩm bán chạy, biểu đồ |
| `/admin/products` | Danh sách sản phẩm — tìm kiếm, lọc theo danh mục/trạng thái |
| `/admin/products/create` | Thêm sản phẩm mới (form + AI hỗ trợ) |
| `/admin/categories` | Quản lý danh mục, thêm/sửa/xóa |
| `/admin/orders` | Danh sách đơn hàng, lọc theo trạng thái |
| `/admin/orders/view/{id}` | Chi tiết đơn hàng, cập nhật trạng thái, in PDF |
| `/admin/customers` | Danh sách khách hàng, thống kê chi tiêu, lọc theo trạng thái |
| `/admin/inventory` | Quản lý tồn kho — nhập hàng, điều chỉnh số lượng |
| `/admin/staff` | Quản lý nhân sự (Admin only) — tạo/sửa/khóa tài khoản |
| `/admin/stats` | Thống kê doanh thu theo ngày/tháng/năm, top sản phẩm |
| `/admin/logs` | Audit log — lịch sử thao tác toàn bộ hệ thống |
| `/admin/ai/generator` | AI Generator — tạo tên/mô tả sản phẩm, tìm ảnh, xóa nền |
| `/admin/ai/assistant` | AI Assistant — chatbot hỗ trợ nghiệp vụ |
| `/admin/ai/report` | Báo cáo AI — phân tích sản phẩm, đề xuất giá |
| `/admin/assets` | Quản lý banner trang chủ và ảnh showcase |
| `/admin/telegram-bot` | Cấu hình Telegram bot, test gửi thông báo |

---

## Phân quyền

| Role | Giá trị | Quyền chi tiết |
|---|---|---|
| Admin | 1 | Toàn quyền — bao gồm xóa, quản lý nhân sự, xem logs |
| Manager | 2 | Tạo + sửa tất cả (sản phẩm, đơn hàng, danh mục, tồn kho); **không xóa** |
| Staff | 3 | Chỉ tạo/sửa **sản phẩm** trong vòng **15 phút** sau khi tạo |

**Bảng so sánh chi tiết:**

| Chức năng | Admin | Manager | Staff |
|---|:---:|:---:|:---:|
| Xem dashboard | ✓ | ✓ | ✓ |
| Thêm sản phẩm | ✓ | ✓ | ✓ |
| Sửa sản phẩm | ✓ | ✓ | ✓ (15 phút) |
| Xóa sản phẩm | ✓ | — | — |
| Quản lý đơn hàng | ✓ | ✓ | — |
| Quản lý khách hàng | ✓ | ✓ | — |
| Quản lý danh mục | ✓ | ✓ | — |
| Quản lý tồn kho | ✓ | ✓ | — |
| Xem thống kê | ✓ | ✓ | — |
| Xem audit logs | ✓ | — | — |
| Quản lý nhân sự | ✓ | — | — |
| AI Generator | ✓ | ✓ | ✓ |

### Tạo tài khoản Manager / Staff

Chỉ Admin mới thực hiện được:

1. Vào `/admin/staff`
2. Nhấn **Thêm nhân sự**
3. Điền họ tên, email, số điện thoại, mật khẩu (tối thiểu 6 ký tự)
4. Chọn **Vai trò**: Manager hoặc Staff
5. Nhấn **Lưu**

Để khóa / mở khóa tài khoản: nhấn nút toggle trên danh sách nhân sự.  
Để đặt lại mật khẩu: nhấn **Reset mật khẩu** trong form sửa.

> Không thể thay đổi vai trò hoặc tự khóa tài khoản đang đăng nhập.

---

## API nội bộ

Tất cả endpoint dưới `/api/{action}`. Request body: JSON. Response luôn HTTP 200:

```json
{ "success": true/false, ... }
```

Nhóm endpoint: `auth`, `cart`, `coupon`, `review`, `ai` (generate, save-image, search-image, remove-bg, add-watermark, check-duplicate, save-product, upload-extra-images, reorder-images, update-extra-image).

---

## Bảo mật

- Luôn dùng parameterized queries — không bao giờ nội suy input vào SQL
- `config/`, `deploy/`, `database/`, `storage/` bị chặn bởi `.htaccess` trên production
- `key.pem` và `.env.local` có trong `.gitignore`, không commit lên repo
- `display_errors` tắt hoàn toàn trên production
- Mật khẩu hash bằng `password_hash()` / `password_verify()`
- Session name tùy chỉnh: `TH_SESS`
- API keys nhạy cảm nên đặt qua biến môi trường, không hardcode trong code

---

## Giấy phép

Dự án nội bộ — Tuấn Huy Computer.
