<?php
/**
 * AITools — Database tools dành cho Claude AI tool_use
 */
class AITools {

    private static $blockedPatterns = array(
        '/\bDROP\b/i', '/\bTRUNCATE\b/i', '/\bALTER\b/i',
        '/\bCREATE\b/i', '/\bRENAME\b/i',
    );

    /**
     * Thực thi SELECT — tối đa 50 hàng, trả về JSON
     */
    public static function queryDb($sql, $params = array()) {
        $sql = trim($sql);
        if (!preg_match('/^SELECT\s/i', $sql)) {
            return json_encode(array('error' => 'queryDb chỉ cho phép câu lệnh SELECT.'));
        }
        if (preg_match('/\b(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER)\b/i', $sql)) {
            return json_encode(array('error' => 'SQL chứa từ khóa ghi không được phép trong queryDb.'));
        }
        try {
            $db = Database::getInstance();
            if (!preg_match('/\bLIMIT\s+\d+/i', $sql)) $sql .= ' LIMIT 50';
            $rows = $db->fetchAll($sql, $params ? $params : array());
            return json_encode(array('rows' => $rows, 'count' => count($rows)), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            return json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * Thực thi INSERT/UPDATE/DELETE — ghi log vào action_logs
     */
    public static function executeDb($sql, $params = array(), $actor = 'AIAgent') {
        $sql = trim($sql);
        if (preg_match('/^SELECT\s/i', $sql)) {
            return json_encode(array('error' => 'Dùng query_db cho SELECT.'));
        }
        foreach (self::$blockedPatterns as $pat) {
            if (preg_match($pat, $sql)) {
                return json_encode(array('error' => 'Lệnh SQL bị chặn vì lý do bảo mật.'));
            }
        }
        if (!preg_match('/^\s*(INSERT|UPDATE|DELETE)\s/i', $sql)) {
            return json_encode(array('error' => 'executeDb chỉ cho phép INSERT, UPDATE, DELETE.'));
        }
        try {
            $db   = Database::getInstance();
            $stmt = $db->query($sql, $params ? $params : array());
            $affected = $stmt->rowCount();
            $db->query(
                "INSERT INTO action_logs (user_name, user_role, action, table_name, target_id, new_data, ip_address)
                 VALUES (?, 0, 'AI_EXECUTE', 'ai_tools', 0, ?, '127.0.0.1')",
                array($actor, json_encode(array('sql' => $sql, 'params' => $params), JSON_UNESCAPED_UNICODE))
            );
            return json_encode(array('success' => true, 'affected_rows' => $affected));
        } catch (Exception $e) {
            return json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * Trả về schema (bảng + cột) từ INFORMATION_SCHEMA
     */
    public static function getSchema() {
        try {
            $db     = Database::getInstance();
            $dbRow  = $db->fetch("SELECT DATABASE() AS db");
            $dbName = $dbRow ? $dbRow['db'] : '';
            $cols   = $db->fetchAll(
                "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ?
                 ORDER BY TABLE_NAME, ORDINAL_POSITION",
                array($dbName)
            );
            $schema = array();
            foreach ($cols as $col) {
                $t = $col['TABLE_NAME'];
                if (!isset($schema[$t])) $schema[$t] = array();
                $schema[$t][] = $col['COLUMN_NAME'] . ' ' . $col['COLUMN_TYPE']
                    . ($col['COLUMN_KEY'] === 'PRI' ? ' [PK]' : '')
                    . ($col['IS_NULLABLE'] === 'NO' ? ' NOT NULL' : '');
            }
            return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            return json_encode(array('error' => $e->getMessage()));
        }
    }

    /**
     * Trả về định nghĩa tools theo format Claude API
     */
    public static function getToolDefinitions() {
        return array(
            array(
                'name'        => 'query_db',
                'description' => 'Thực thi câu lệnh SELECT trên MySQL. Chỉ đọc, không thay đổi dữ liệu. Trả về tối đa 50 hàng dạng JSON.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'sql'    => array('type' => 'string', 'description' => 'Câu lệnh SELECT (bắt buộc bắt đầu bằng SELECT)'),
                        'params' => array('type' => 'array',  'description' => 'Mảng tham số prepared statement (tùy chọn)', 'items' => array('type' => 'string')),
                    ),
                    'required' => array('sql'),
                ),
            ),
            array(
                'name'        => 'execute_db',
                'description' => 'Thực thi INSERT, UPDATE hoặc DELETE. Tự động ghi audit log. Không cho phép DROP/TRUNCATE/ALTER.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'sql'    => array('type' => 'string', 'description' => 'Câu lệnh INSERT, UPDATE hoặc DELETE'),
                        'params' => array('type' => 'array',  'description' => 'Mảng tham số prepared statement', 'items' => array('type' => 'string')),
                    ),
                    'required' => array('sql'),
                ),
            ),
            array(
                'name'        => 'get_schema',
                'description' => 'Trả về danh sách tất cả bảng và cột trong database kèm kiểu dữ liệu. Dùng trước khi viết SQL để biết cấu trúc.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => new stdClass(),
                    'required'   => array(),
                ),
            ),
        );
    }
}
