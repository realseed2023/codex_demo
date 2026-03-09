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

    public function create(array $payload): array
    {
        return $this->driver === 'mysql' ? $this->createInMysql($payload) : $this->createInJson($payload);
    }

    private function allFromJson(): array
    {
        $rows = $this->readJson();
        usort($rows, static fn(array $a, array $b): int => (int) $b['id'] <=> (int) $a['id']);
        return $rows;
    }

    private function createInJson(array $payload): array
    {
        $rows = $this->readJson();
        $ids = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $rows);
        $nextId = ($ids !== [] ? max($ids) : 100) + 1;
        $payload['id'] = $nextId;
        $rows[] = $payload;
        $this->writeJson($rows);

        return $payload;
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

        $stmt = $pdo->query('SELECT id,table_code,status,items,total_amount,created_at FROM preorders ORDER BY id DESC');
        $rows = $stmt->fetchAll();

        return array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'table_code' => (string) $row['table_code'],
                'status' => (string) $row['status'],
                'items' => json_decode((string) $row['items'], true) ?: [],
                'total_amount' => (float) $row['total_amount'],
                'created_at' => date('c', strtotime((string) $row['created_at'])),
            ];
        }, $rows);
    }

    private function createInMysql(array $payload): array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $stmt = $pdo->prepare('INSERT INTO preorders (table_code,status,items,total_amount,created_at) VALUES (:table_code,:status,:items,:total_amount,:created_at)');

        $createdAtMysql = date('Y-m-d H:i:s', strtotime((string) $payload['created_at']));
        $stmt->execute([
            ':table_code' => (string) $payload['table_code'],
            ':status' => (string) $payload['status'],
            ':items' => json_encode($payload['items'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':total_amount' => (float) $payload['total_amount'],
            ':created_at' => $createdAtMysql,
        ]);

        $payload['id'] = (int) $pdo->lastInsertId();
        $payload['created_at'] = date('c', strtotime($createdAtMysql));

        return $payload;
    }

    private function ensureMysqlSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS preorders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            table_code VARCHAR(64) NOT NULL,
            status VARCHAR(32) NOT NULL,
            items JSON NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
