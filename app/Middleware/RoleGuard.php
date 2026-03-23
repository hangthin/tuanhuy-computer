<?php
/**
 * RoleGuard — RBAC middleware
 *
 * Role hierarchy:
 *   1 = Admin   — full access (create / edit / delete / view logs)
 *   2 = Manager — create & edit; NO delete of any kind
 *   3 = Staff   — create products; edit products only within 15 min of creation
 */
class RoleGuard {

    const ADMIN   = 1;
    const MANAGER = 2;
    const STAFF   = 3;

    /** Current user's role integer */
    public static function role(): int {
        return (int)($_SESSION['user_role'] ?? 0);
    }

    /** Require at least Staff level; redirect guests / plain customers */
    public static function requireStaffOrAbove(): void {
        if (!isLoggedIn() || !in_array(self::role(), [self::ADMIN, self::MANAGER, self::STAFF])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    /** Can the current user create records in $table? */
    public static function canCreate(string $table): bool {
        $r = self::role();
        if ($r === self::ADMIN || $r === self::MANAGER) return true;
        if ($r === self::STAFF) {
            return in_array($table, ['products', 'product_images']);
        }
        return false;
    }

    /**
     * Can the current user edit a record?
     *
     * @param string      $table      e.g. 'products'
     * @param string|null $createdAt  DB timestamp of the record (for Staff check)
     */
    public static function canEdit(string $table, ?string $createdAt = null): bool {
        $r = self::role();
        if ($r === self::ADMIN || $r === self::MANAGER) return true;
        if ($r === self::STAFF && $table === 'products' && $createdAt !== null) {
            $elapsed = time() - strtotime($createdAt);
            return $elapsed <= 900; // 15 minutes = 900 seconds
        }
        return false;
    }

    /** Can the current user soft-delete records? Only Admin. */
    public static function canDelete(): bool {
        return self::role() === self::ADMIN;
    }

    /**
     * Abort with a flash error and redirect.
     * Tracks repeated denied attempts; auto-locks account after threshold.
     */
    public static function deny(string $msg = 'Bạn không có quyền thực hiện hành động này.'): void {
        // Track denied access and potentially lock account
        self::trackDeniedAccess();

        setFlash('error', $msg);
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : APP_URL . '/admin/products';
        header('Location: ' . $back);
        exit;
    }

    /**
     * Log an ACCESS_DENIED event and auto-lock the account if
     * the same Staff user exceeds the threshold within the window.
     *
     * Threshold : 3 attempts within 30 minutes → lock + Zalo alert
     */
    public static function trackDeniedAccess(): void {
        // Only track logged-in staff (role ≥ 1)
        if (!isLoggedIn() || self::role() < 1) return;

        $userId = (int)$_SESSION['user_id'];
        $page   = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'unknown';

        try {
            $db = Database::getInstance();

            // 1. Log this attempt
            $db->query(
                "INSERT INTO action_logs
                    (user_id, user_name, user_role, action, table_name, target_id, new_data, ip_address)
                 VALUES (?, ?, ?, 'ACCESS_DENIED', 'security', NULL, ?, ?)",
                array(
                    $userId,
                    $_SESSION['user_name'] ?? 'unknown',
                    self::role(),
                    json_encode(array('page' => mb_substr($page, 0, 200, 'UTF-8'))),
                    self::clientIp(),
                )
            );

            // 2. Count recent attempts (last 30 min, this user)
            $count = (int)$db->query(
                "SELECT COUNT(*) FROM action_logs
                 WHERE user_id = ? AND action = 'ACCESS_DENIED'
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
                array($userId)
            )->fetchColumn();

            // 3. Auto-lock if over threshold
            if ($count >= 3) {
                $already = $db->fetch("SELECT is_active FROM users WHERE id=?", array($userId));
                if ($already && $already['is_active']) {
                    $db->query("UPDATE users SET is_active=0 WHERE id=?", array($userId));

                    $db->query(
                        "INSERT INTO action_logs
                            (user_id, user_name, user_role, action, table_name, target_id, new_data, ip_address)
                         VALUES (?, ?, 1, 'UPDATE', 'users', ?, ?, ?)",
                        array(
                            $userId,
                            'System',
                            $userId,
                            json_encode(array('is_active' => 0, 'reason' => 'auto_locked_access_denied')),
                            self::clientIp(),
                        )
                    );

                    // Send lockout notification (Telegram + Zalo, non-blocking)
                    try {
                        $u = $db->fetch("SELECT fullname, email FROM users WHERE id=?", array($userId));
                        if ($u) {
                            $reason = "Vượt quá 3 lần truy cập trái phép trong 30 phút";

                            $tgFile = __DIR__ . '/../Helpers/TelegramNotifier.php';
                            if (file_exists($tgFile)) {
                                require_once $tgFile;
                                TelegramNotifier::notifyLockout($u['fullname'], $u['email'], $reason);
                            }

                            $zaloFile = __DIR__ . '/../Helpers/ZaloNotifier.php';
                            if (file_exists($zaloFile)) {
                                require_once $zaloFile;
                                if (class_exists('ZaloNotifier') && ZaloNotifier::isConfigured()) {
                                    ZaloNotifier::notifyLockout($u['fullname'], $u['email'], $reason);
                                }
                            }
                        }
                    } catch (Exception $ze) {
                        // Notification failure must not break app
                    }

                    // Destroy session so they're immediately logged out
                    session_destroy();
                    header('Location: ' . APP_URL . '/auth/login?locked=1');
                    exit;
                }
            }
        } catch (Exception $e) {
            // Security tracking must never crash the app
            error_log('[RoleGuard::trackDeniedAccess] ' . $e->getMessage());
        }
    }

    private static function clientIp() {
        foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $k) {
            if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
        }
        return '';
    }

    /** Human-readable label for a role integer */
    public static function label(?int $role = null): string {
        $map = [
            self::ADMIN   => 'Admin',
            self::MANAGER => 'Manager',
            self::STAFF   => 'Staff',
            0             => 'Khách hàng',
        ];
        return $map[$role ?? self::role()] ?? 'Unknown';
    }

    /**
     * Returns remaining editable seconds for a Staff user on a product.
     * Returns null if not applicable.
     */
    public static function staffEditSecondsLeft(?string $createdAt): ?int {
        if (self::role() !== self::STAFF || $createdAt === null) return null;
        $remaining = 900 - (time() - strtotime($createdAt));
        return max(0, $remaining);
    }
}
