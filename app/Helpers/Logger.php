<?php
/**
 * Logger — Activity audit trail
 *
 * Usage:
 *   Logger::log('CREATE', 'products', $newId, null, $newData);
 *   Logger::log('UPDATE', 'products', $id, $oldData, $newData);
 *   Logger::log('DELETE', 'products', $id, $oldData, null);
 */
class Logger {

    private static $db = null;

    private static function db(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Write one audit row.
     *
     * @param string     $action    CREATE | UPDATE | DELETE | LOGIN | LOGOUT
     * @param string     $table     DB table name, e.g. 'products'
     * @param int|null   $targetId  Primary key of affected row
     * @param mixed      $oldData   Snapshot before change (array|null)
     * @param mixed      $newData   Snapshot after change  (array|null)
     */
    public static function log(
        string $action,
        string $table,
        ?int   $targetId = null,
        $oldData = null,
        $newData = null
    ): void {
        try {
            $userId   = isset($_SESSION['user_id'])   ? (int)$_SESSION['user_id']   : null;
            $userName = isset($_SESSION['user_name'])  ? $_SESSION['user_name']       : 'System';
            $role     = isset($_SESSION['user_role'])  ? (int)$_SESSION['user_role']  : 0;
            $ip       = self::clientIp();

            // Strip sensitive / bulky fields before storing
            if (is_array($oldData)) $oldData = self::sanitizeSnapshot($oldData);
            if (is_array($newData)) $newData = self::sanitizeSnapshot($newData);

            self::db()->query(
                "INSERT INTO action_logs
                    (user_id, user_name, user_role, action, table_name, target_id, old_data, new_data, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $userName,
                    $role,
                    $action,
                    $table,
                    $targetId,
                    $oldData !== null ? json_encode($oldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    $newData !== null ? json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    $ip,
                ]
            );
        } catch (Exception $e) {
            // Logging must never crash the application
            error_log('[Logger] ' . $e->getMessage());
        }
    }

    // ── Convenience wrappers ─────────────────────────────────

    public static function create(string $table, int $id, array $data): void {
        self::log('CREATE', $table, $id, null, $data);
    }

    public static function update(string $table, int $id, array $old, array $new, array $context = []): void {
        // Only log if something actually changed
        $diff = self::diff($old, $new);
        if (!empty($diff['old']) || !empty($diff['new'])) {
            // Context fields (e.g. name/fullname) are always stored in both sides
            // so the log viewer can display who/what was affected even if those fields didn't change.
            $logOld = !empty($context) ? array_merge($context, $diff['old']) : $diff['old'];
            $logNew = !empty($context) ? array_merge($context, $diff['new']) : $diff['new'];
            self::log('UPDATE', $table, $id, $logOld, $logNew);
        }
    }

    public static function delete(string $table, int $id, array $old): void {
        self::log('DELETE', $table, $id, $old, null);
    }

    /**
     * Log a named image/processing activity.
     * $type: 'IMG_UPDATE' | 'IMG_REMOVE_BG' | 'IMG_LOGO' | 'IMG_EXTRA_ADD' | 'IMG_DELETE'
     */
    public static function logActivity(string $type, string $table, ?int $targetId = null, array $data = []): void {
        self::log($type, $table, $targetId, null, !empty($data) ? $data : null);
    }

    // ── Fetch helpers (for admin log viewer) ────────────────

    /** Paginated log entries, newest first */
    public static function getAll(int $page = 1, int $limit = 50, array $filters = []): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[]  = 'action = ?';
            $params[] = strtoupper($filters['action']);
        }
        if (!empty($filters['table'])) {
            $where[]  = 'table_name = ?';
            $params[] = $filters['table'];
        }
        if (!empty($filters['user_id'])) {
            $where[]  = 'user_id = ?';
            $params[] = (int)$filters['user_id'];
        }

        $ws     = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        try {
            return Database::getInstance()->fetchAll(
                "SELECT * FROM action_logs WHERE {$ws} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
                $params
            );
        } catch (Exception $e) {
            return [];
        }
    }

    public static function count(array $filters = []): int {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['action'])) { $where[] = 'action = ?';     $params[] = strtoupper($filters['action']); }
        if (!empty($filters['table']))  { $where[] = 'table_name = ?'; $params[] = $filters['table']; }
        if (!empty($filters['user_id'])){ $where[] = 'user_id = ?';    $params[] = (int)$filters['user_id']; }
        $ws = implode(' AND ', $where);
        try {
            return (int)Database::getInstance()
                ->query("SELECT COUNT(*) FROM action_logs WHERE {$ws}", $params)
                ->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    // ── Private helpers ──────────────────────────────────────

    /** Return only changed keys between old and new snapshots */
    private static function diff(array $old, array $new): array {
        $changedOld = [];
        $changedNew = [];
        $allKeys    = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $k) {
            $o = $old[$k] ?? null;
            $n = $new[$k] ?? null;
            if ((string)$o !== (string)$n) {
                $changedOld[$k] = $o;
                $changedNew[$k] = $n;
            }
        }
        return ['old' => $changedOld, 'new' => $changedNew];
    }

    /** Remove password and large blob fields before logging */
    private static function sanitizeSnapshot(array $data): array {
        $skip = ['password', 'image_base64', 'extra_b64', 'description'];
        foreach ($skip as $k) unset($data[$k]);

        // Truncate very long strings (e.g. description kept short)
        foreach ($data as $k => $v) {
            if (is_string($v) && strlen($v) > 500) {
                $data[$k] = mb_substr($v, 0, 500, 'UTF-8') . '…';
            }
        }
        return $data;
    }

    private static function clientIp(): string {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '';
    }
}
