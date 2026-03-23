<?php
// config/database.php
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    'rootroot');
define('DB_NAME',    'mpc');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET NAMES utf8mb4");
            $this->pdo->exec("SET CHARACTER SET utf8mb4");
        } catch (PDOException $e) {
            die('<div style="font-family:Arial;padding:25px;background:#fee2e2;color:#991b1b;border-left:4px solid red;margin:20px;border-radius:8px">
                <h3>❌ Lỗi kết nối Database</h3>
                <p><strong>Lỗi:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Kiểm tra: <code>config/database.php</code> — DB_PASS và DB_NAME</p>
                <p>Database hiện tại: <code>' . DB_NAME . '</code> | User: <code>' . DB_USER . '</code></p>
            </div>');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }
    public function getConnection() { return $this->pdo; }
    public function query($sql, $params = array()) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public function fetchAll($sql, $params = array()) {
        return $this->query($sql, $params)->fetchAll();
    }
    public function fetch($sql, $params = array()) {
        $result = $this->query($sql, $params)->fetch();
        return $result ? $result : null;
    }
    public function lastInsertId() { return $this->pdo->lastInsertId(); }
}
