<?php
require_once __DIR__.'/../../config/database.php';

class SseController {

    public function orders($p = null): void {
        // Auth check (session already started by config/app.php via router)
        $role = (int)($_SESSION['user_role'] ?? -1);
        if (!isset($_SESSION['user_id']) || !in_array($role, array(1, 2, 3))) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        // Release session lock so other requests aren't blocked
        session_write_close();

        // Disable output buffering
        while (ob_get_level()) ob_end_clean();

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        $db     = Database::getInstance();
        $lastId = max(0, (int)($_GET['last_id'] ?? 0));

        // If no baseline, send current max and exit (client reconnects with it)
        if (!$lastId) {
            $row    = $db->fetch("SELECT MAX(id) AS m FROM orders WHERE is_deleted=0");
            $lastId = (int)($row['m'] ?? 0);
            echo "data: " . json_encode(array('type' => 'init', 'last_id' => $lastId)) . "\n\n";
            flush();
            return;
        }

        $maxCycles = 60; // max 5 min per connection; client reconnects automatically
        $cycle     = 0;

        while ($cycle < $maxCycles) {
            if (connection_aborted()) break;

            $newOrders = $db->fetchAll(
                "SELECT id, order_code, fullname, phone, total, status, created_at
                 FROM orders WHERE id > ? AND is_deleted=0 ORDER BY id ASC",
                array($lastId)
            );

            if (!empty($newOrders)) {
                $lastId = (int)end($newOrders)['id'];
                echo "data: " . json_encode(array(
                    'type'    => 'orders',
                    'orders'  => $newOrders,
                    'last_id' => $lastId,
                )) . "\n\n";
                flush();
            } else {
                // Heartbeat comment to keep connection alive
                echo ": ping\n\n";
                flush();
            }

            sleep(5);
            $cycle++;
        }
    }

    public function index($p = null): void { $this->orders($p); }
}
