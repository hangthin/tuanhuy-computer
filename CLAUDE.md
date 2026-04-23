# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Tuấn Huy Computer** — PHP e-commerce site for a computer hardware store. No framework, no Composer, no npm. Pure PHP + MySQL.

- Local: `http://localhost/tuanhuy_computer`
- Production: Docker (PHP 8.1-Apache) on Oracle Cloud / Render

## Running Locally

Requires: AppServ or XAMPP (Apache + PHP 7.4+/8.1 + MySQL), `mod_rewrite` enabled.

1. Place project in Apache web root (e.g. `C:\AppServ\www\tuanhuy_computer`)
2. Create MySQL database `mpc`, import `database/migrations.sql`
3. Copy `config/app.php` and fill in API keys, SMTP, DB credentials
4. Create writable directories: `uploads/products/`, `storage/`
5. Visit `http://localhost/tuanhuy_computer`

No build step. No compilation. No package manager.

## Routing

All requests → `index.php` (via `.htaccess` rewrite).

URL pattern: `/{controller}/{action}/{param}`

- Kebab-case action segments are camelCased: `cancel-order` → `cancelOrder()`
- `/products/{slug}` → `ProductController->index($slug)` — uses `$actionRaw` (not camelCased) as `$param`
- `/api/{action}/{param}` → `ApiController->{action}($param)`
- `/admin/ai/generator` → `AdminController->ai('generator')`

Controllers map: `home`, `products`, `cart`, `checkout`, `auth`, `account`, `admin`, `api`, `search`.

## Database

Singleton PDO in `config/database.php`:

```php
$db = Database::getInstance();
$db->fetch("SELECT * FROM users WHERE id=?", [$id]);      // single row or null
$db->fetchAll("SELECT * FROM products WHERE ...", [...]);  // array of rows
$db->query("UPDATE ...", [...]);                           // PDOStatement
$db->lastInsertId();
```

Always use parameterized queries. Never interpolate user input into SQL.

## Auth & Sessions

Session name: `TH_SESS`. Key session vars: `user_id`, `user_name`, `user_email`, `user_role`.

Helper functions (defined in `config/app.php`): `isLoggedIn()`, `isAdmin()`, `isStaff()`, `requireLogin()`, `requireAdmin()`, `sanitize($str)`, `setFlash($type, $msg)`, `getFlash()`, `formatPrice($n)`, `makeSlug($str)`.

## Role System (`app/Middleware/RoleGuard.php`)

| Role | Value | Permissions |
|---|---|---|
| Admin | 1 | Everything |
| Manager | 2 | Create + edit all; no delete |
| Staff | 3 | Create/edit products only, within 15 min of creation |

Key methods: `RoleGuard::requireStaffOrAbove()`, `canCreate($table)`, `canEdit($table, $createdAt)`, `canDelete()`.

## API Endpoints

All under `/api/{action}/{param}`. Request body: JSON. Response: always HTTP 200 with `{"success": bool, ...}`.

Read input: `json_decode(file_get_contents('php://input'), true)`.

Groups: `auth` (login/register), `cart` (add/update/remove), `coupon`, `review`, `ai` (generate, save-image, search-image, remove-bg, add-watermark, check-duplicate, save-product, upload-extra-images, reorder-images, update-extra-image).

## Audit Logging

```php
Logger::create('products', $id, $data);
Logger::update('products', $id, $oldData, $newData);  // only diffs stored
Logger::delete('products', $id, $oldData);
Logger::log('LOGIN', 'users', $id, null, ['email' => ...]);
```

Stored in `action_logs` table.

## Flash Messages

```php
setFlash('success', 'Saved!');           // persists one redirect
setFlash('success_center', 'Title', ['sub' => '...', 'icon' => '✅']);
setFlash('error', 'Something failed.');
// Rendered automatically in app/Views/admin/layout_bottom.php
```

## AI Features

Groq API (key: `AI_API_KEY`). Vision model for image analysis, text model for name-to-product generation. Background removal uses `@imgly/background-removal` browser-side (ESM from esm.sh). Image search: SerpApi → Bing → Pexels → Pixabay → Google fallback chain.

## Bot & Cron

- `cron.php` — secured by `?token=TELEGRAM_CRON_SECRET`; called externally every minute
- `app/bot_daemon.php` — long-running Telegram poll loop (`php app/bot_daemon.php`)
- `app/bot_tick.php` — single-tick processor; used by Task Scheduler on Windows
- Root `bot_daemon.php` / `bot_tick.php` are stubs that include the `app/` versions

## Key Config Constants

Defined in `config/app.php`: `APP_URL`, `APP_NAME`, `UPLOAD_PATH`, `UPLOAD_URL`, `ITEMS_PER_PAGE`, `AI_API_KEY`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_ADMIN_CHAT`, `MAIL_HOST/PORT/USER/PASS`.

`display_errors` is on only when `APP_URL` contains `localhost`.

## File Locations

| What | Where |
|---|---|
| Router | `index.php` |
| Config | `config/app.php`, `config/database.php` |
| Models | `app/Models/Models.php` (User/Cart/Order/Category), `app/Models/ProductModel.php` |
| Admin views | `app/Views/admin/` |
| Admin layout | `app/Views/admin/layout_top.php`, `layout_bottom.php` |
| Public layout | `app/Views/layouts/header.php`, `footer.php` |
| Uploaded images | `uploads/products/` (excluded from git) |
| Approved assets | `assets/images/approved.json` |
| DB schema | `database/migrations.sql` (full schema + incremental migrations combined) |
