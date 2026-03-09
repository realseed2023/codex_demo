<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class TableRepository
{
    private string $driver;
    private string $file;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $this->driver = strtolower((string) ($config['driver'] ?? 'json'));
        $this->file = dirname(__DIR__, 2) . '/storage/tables.json';
    }

    public function findByCode(string $tableCode): ?array
    {
        return $this->driver === 'mysql' ? $this->findByCodeFromMysql($tableCode) : $this->findByCodeFromJson($tableCode);
    }

    private function findByCodeFromJson(string $tableCode): ?array
    {
        foreach ($this->readJson() as $table) {
            if ((string) ($table['table_code'] ?? '') !== $tableCode) {
                continue;
            }

            return $table;
        }

        return null;
    }

    private function readJson(): array
    {
        if (!is_file($this->file)) {
            return [
                ['id' => 1, 'table_code' => 'A01', 'name' => '大厅A01', 'status' => 'active'],
                ['id' => 2, 'table_code' => 'A02', 'name' => '大厅A02', 'status' => 'active'],
            ];
        }

        $raw = file_get_contents($this->file);
        $decoded = $raw !== false ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function findByCodeFromMysql(string $tableCode): ?array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $stmt = $pdo->prepare('SELECT id,table_code,name,status FROM tables WHERE table_code = :table_code LIMIT 1');
        $stmt->execute([':table_code' => $tableCode]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'table_code' => (string) $row['table_code'],
            'name' => (string) $row['name'],
            'status' => (string) $row['status'],
        ];
    }

    private function ensureMysqlSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tables (
            id INT PRIMARY KEY AUTO_INCREMENT,
            table_code VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            status VARCHAR(32) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $count = (int) $pdo->query('SELECT COUNT(*) FROM tables')->fetchColumn();
        if ($count === 0) {
            $pdo->exec("INSERT INTO tables (table_code,name,status) VALUES
                ('A01','大厅A01','active'),
                ('A02','大厅A02','active')");
        }
    }
}
