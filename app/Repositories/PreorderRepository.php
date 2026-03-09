<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PreorderRepository
{
    private string $driver;
    private string $file;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $this->driver = strtolower((string) ($config['driver'] ?? 'json'));
        $this->file = dirname(__DIR__, 2) . '/storage/preorders.json';
    }

    public function all(): array
    {
        return $this->driver === 'mysql' ? $this->allFromMysql() : $this->allFromJson();
    }

    public function findById(int $id): ?array
    {
        return $this->driver === 'mysql' ? $this->findByIdFromMysql($id) : $this->findByIdFromJson($id);
    }

    public function saveDraft(array $payload): array
    {
        return $this->driver === 'mysql' ? $this->saveDraftInMysql($payload) : $this->saveDraftInJson($payload);
    }

    public function submit(array $payload): array
    {
        return $this->driver === 'mysql' ? $this->submitInMysql($payload) : $this->submitInJson($payload);
    }

    private function allFromJson(): array
    {
        $rows = $this->readJson();
        usort($rows, static fn(array $a, array $b): int => (int) $b['id'] <=> (int) $a['id']);
        return $rows;
    }

    private function findByIdFromJson(int $id): ?array
    {
        foreach ($this->readJson() as $row) {
            if ((int) $row['id'] === $id) {
                return $row;
            }
        }

        return null;
    }

    private function saveDraftInJson(array $payload): array
    {
        $rows = $this->readJson();
        $now = date('c');
        $items = $this->normalizeItems($payload['items']);

        if (!empty($payload['id'])) {
            foreach ($rows as &$row) {
                if ((int) $row['id'] !== (int) $payload['id']) {
                    continue;
                }

                $row['table_id'] = (int) $payload['table_id'];
                $row['table_code'] = (string) $payload['table_code'];
                $row['status'] = 'draft';
                $row['subtotal_amount'] = (float) $payload['subtotal_amount'];
                $row['remark'] = (string) ($payload['remark'] ?? '');
                $row['items'] = $items;
                $row['updated_at'] = $now;
                $this->writeJson($rows);
                return $row;
            }
        }

        $ids = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $rows);
        $nextId = ($ids !== [] ? max($ids) : 1000) + 1;
        $new = [
            'id' => $nextId,
            'table_id' => (int) $payload['table_id'],
            'table_code' => (string) $payload['table_code'],
            'order_no' => null,
            'status' => 'draft',
            'subtotal_amount' => (float) $payload['subtotal_amount'],
            'remark' => (string) ($payload['remark'] ?? ''),
            'items' => $items,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $rows[] = $new;
        $this->writeJson($rows);

        return $new;
    }

    private function submitInJson(array $payload): array
    {
        $rows = $this->readJson();
        $now = date('c');

        if (!empty($payload['id'])) {
            foreach ($rows as &$row) {
                if ((int) $row['id'] !== (int) $payload['id']) {
                    continue;
                }

                $row['status'] = (string) $payload['status'];
                $row['order_no'] = (string) $payload['order_no'];
                $row['remark'] = (string) ($payload['remark'] ?? ($row['remark'] ?? ''));
                $row['updated_at'] = $now;
                $this->writeJson($rows);
                return $row;
            }
        }

        return $this->saveDraftInJson($payload + ['status' => (string) $payload['status']]);
    }

    private function readJson(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $raw = file_get_contents($this->file);
        $decoded = $raw !== false ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function writeJson(array $rows): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function allFromMysql(): array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $stmt = $pdo->query('SELECT id,table_id,order_no,status,subtotal_amount,remark,created_at,updated_at FROM pre_orders ORDER BY id DESC');
        $orders = $stmt->fetchAll();

        return array_map(fn(array $row): array => $this->hydrateMysqlOrder($pdo, $row), $orders);
    }

    private function findByIdFromMysql(int $id): ?array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $stmt = $pdo->prepare('SELECT id,table_id,order_no,status,subtotal_amount,remark,created_at,updated_at FROM pre_orders WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrateMysqlOrder($pdo, $row) : null;
    }

    private function saveDraftInMysql(array $payload): array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);
        $now = date('Y-m-d H:i:s');

        if (!empty($payload['id'])) {
            $stmt = $pdo->prepare('UPDATE pre_orders SET table_id=:table_id, status=:status, subtotal_amount=:subtotal_amount, remark=:remark, updated_at=:updated_at WHERE id=:id');
            $stmt->execute([
                ':id' => (int) $payload['id'],
                ':table_id' => (int) $payload['table_id'],
                ':status' => 'draft',
                ':subtotal_amount' => (float) $payload['subtotal_amount'],
                ':remark' => (string) ($payload['remark'] ?? ''),
                ':updated_at' => $now,
            ]);
            $id = (int) $payload['id'];

            $pdo->prepare('DELETE FROM pre_order_items WHERE pre_order_id=:pre_order_id')->execute([':pre_order_id' => $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO pre_orders (table_id,order_no,status,subtotal_amount,remark,created_at,updated_at) VALUES (:table_id,:order_no,:status,:subtotal_amount,:remark,:created_at,:updated_at)');
            $stmt->execute([
                ':table_id' => (int) $payload['table_id'],
                ':order_no' => null,
                ':status' => 'draft',
                ':subtotal_amount' => (float) $payload['subtotal_amount'],
                ':remark' => (string) ($payload['remark'] ?? ''),
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
            $id = (int) $pdo->lastInsertId();
        }

        $itemStmt = $pdo->prepare('INSERT INTO pre_order_items (pre_order_id,menu_item_id,item_name_snapshot,unit_price_snapshot,quantity,line_amount) VALUES (:pre_order_id,:menu_item_id,:item_name_snapshot,:unit_price_snapshot,:quantity,:line_amount)');
        foreach ($this->normalizeItems($payload['items']) as $item) {
            $itemStmt->execute([
                ':pre_order_id' => $id,
                ':menu_item_id' => (int) $item['menu_item_id'],
                ':item_name_snapshot' => (string) $item['item_name_snapshot'],
                ':unit_price_snapshot' => (float) $item['unit_price_snapshot'],
                ':quantity' => (int) $item['quantity'],
                ':line_amount' => (float) $item['line_amount'],
            ]);
        }

        return $this->findByIdFromMysql($id) ?? [];
    }

    private function submitInMysql(array $payload): array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $stmt = $pdo->prepare('UPDATE pre_orders SET order_no=:order_no,status=:status,remark=:remark,updated_at=:updated_at WHERE id=:id');
        $stmt->execute([
            ':id' => (int) $payload['id'],
            ':order_no' => (string) $payload['order_no'],
            ':status' => (string) $payload['status'],
            ':remark' => (string) ($payload['remark'] ?? ''),
            ':updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->findByIdFromMysql((int) $payload['id']) ?? [];
    }

    private function hydrateMysqlOrder(PDO $pdo, array $row): array
    {
        $itemStmt = $pdo->prepare('SELECT menu_item_id,item_name_snapshot,unit_price_snapshot,quantity,line_amount FROM pre_order_items WHERE pre_order_id=:pre_order_id ORDER BY id ASC');
        $itemStmt->execute([':pre_order_id' => (int) $row['id']]);
        $items = $itemStmt->fetchAll();

        return [
            'id' => (int) $row['id'],
            'table_id' => (int) $row['table_id'],
            'order_no' => $row['order_no'] !== null ? (string) $row['order_no'] : null,
            'status' => (string) $row['status'],
            'subtotal_amount' => (float) $row['subtotal_amount'],
            'remark' => (string) $row['remark'],
            'items' => array_map(fn(array $item): array => [
                'menu_item_id' => (int) $item['menu_item_id'],
                'item_name_snapshot' => (string) $item['item_name_snapshot'],
                'unit_price_snapshot' => (float) $item['unit_price_snapshot'],
                'quantity' => (int) $item['quantity'],
                'line_amount' => (float) $item['line_amount'],
            ], $items),
            'created_at' => date('c', strtotime((string) $row['created_at'])),
            'updated_at' => date('c', strtotime((string) $row['updated_at'])),
        ];
    }

    private function normalizeItems(array $items): array
    {
        return array_map(static fn(array $item): array => [
            'menu_item_id' => (int) $item['menu_item_id'],
            'item_name_snapshot' => (string) $item['item_name_snapshot'],
            'unit_price_snapshot' => (float) $item['unit_price_snapshot'],
            'quantity' => (int) $item['quantity'],
            'line_amount' => (float) $item['line_amount'],
        ], $items);
    }

    private function ensureMysqlSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tables (
            id INT PRIMARY KEY AUTO_INCREMENT,
            table_code VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            status VARCHAR(32) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS pre_orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            table_id INT NOT NULL,
            order_no VARCHAR(64) NULL,
            status VARCHAR(32) NOT NULL COMMENT 'draft/submitted/pending_payment/paid/confirmed/completed/cancelled',
            subtotal_amount DECIMAL(10,2) NOT NULL,
            remark VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_pre_orders_table_id (table_id),
            CONSTRAINT fk_pre_orders_table FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS pre_order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            pre_order_id INT NOT NULL,
            menu_item_id INT NOT NULL,
            item_name_snapshot VARCHAR(120) NOT NULL,
            unit_price_snapshot DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL,
            line_amount DECIMAL(10,2) NOT NULL,
            INDEX idx_pre_order_items_pre_order_id (pre_order_id),
            CONSTRAINT fk_pre_order_items_pre_order FOREIGN KEY (pre_order_id) REFERENCES pre_orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


        $pdo->exec("CREATE TABLE IF NOT EXISTS payment_records (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            channel VARCHAR(32) NOT NULL,
            out_trade_no VARCHAR(64) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(32) NOT NULL,
            raw_payload JSON NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uk_payment_records_out_trade_no (out_trade_no),
            INDEX idx_payment_records_order_id (order_id),
            CONSTRAINT fk_payment_records_order FOREIGN KEY (order_id) REFERENCES pre_orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
